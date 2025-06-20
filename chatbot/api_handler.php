<?php
require_once '../config/config.php';
require_once 'simple_chatbot.php';
require_once 'smart_chatbot.php';

// Set JSON response header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$database = new Database();
$db = $database->getConnection();

// Function to call Python chatbot API
function callChatbotAPI($question, $session_id = null, $user_id = null) {
    $chatbot_url = 'http://localhost:8000/ask'; // URL of your Python FastAPI
    
    $data = json_encode([
        'question' => $question,
        'session_id' => $session_id,
        'user_id' => $user_id
    ]);
    
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => $data,
            'timeout' => 30
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($chatbot_url, false, $context);
    
    if ($result === FALSE) {
        // Use smart PHP chatbot if Python service is not available
        $smart_chatbot = new SmartChatbot($GLOBALS['db']);
        $smart_response = $smart_chatbot->getSmartResponse($question, $user_id);
        return [
            'success' => true,
            'response' => $smart_response,
            'fallback' => true,
            'smart' => true
        ];
    }
    
    $response = json_decode($result, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'Invalid response from chatbot service: ' . $result
        ];
    }
    
    return [
        'success' => true,
        'response' => $response['response'] ?? 'No response received',
        'session_id' => $response['session_id'] ?? $session_id
    ];
}

// Function to save conversation to database
function saveConversation($db, $user_id, $session_id, $user_message, $bot_response) {
    try {
        // Insert conversation
        $insert_query = "INSERT INTO chat_conversations (user_id, session_id, user_message, bot_response) VALUES (?, ?, ?, ?)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->execute([$user_id, $session_id, $user_message, $bot_response]);
        
        // Update session
        $update_session = "INSERT INTO chat_sessions (session_id, user_id, total_messages) VALUES (?, ?, 1) 
                          ON DUPLICATE KEY UPDATE total_messages = total_messages + 1, last_activity = NOW()";
        $session_stmt = $db->prepare($update_session);
        $session_stmt->execute([$session_id, $user_id]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error saving conversation: " . $e->getMessage());
        return false;
    }
}

// Function to get conversation history
function getConversationHistory($db, $session_id, $limit = 10) {
    try {
        $query = "SELECT user_message, bot_response, created_at 
                  FROM chat_conversations 
                  WHERE session_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$session_id, $limit]);
        return array_reverse($stmt->fetchAll()); // Reverse to show oldest first
    } catch (Exception $e) {
        error_log("Error getting conversation history: " . $e->getMessage());
        return [];
    }
}

// Main API logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['question']) || empty(trim($input['question']))) {
        echo json_encode([
            'success' => false,
            'error' => 'Question is required'
        ]);
        exit;
    }
    
    $question = trim($input['question']);
    $session_id = $input['session_id'] ?? 'session_' . uniqid();
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    // Try Python chatbot first, then smart PHP chatbot
    $chatbot_result = callChatbotAPI($question, $session_id, $user_id);
    
    if (!$chatbot_result['success']) {
        // If Python fails, use smart chatbot
        $smart_chatbot = new SmartChatbot($db);
        $smart_response = $smart_chatbot->getSmartResponse($question, $user_id);
        $chatbot_result = [
            'success' => true,
            'response' => $smart_response,
            'fallback' => true,
            'smart' => true
        ];
    }
    
    $bot_response = $chatbot_result['response'];
    $returned_session_id = $chatbot_result['session_id'] ?? $session_id;
    
    // Save conversation to database
    $saved = saveConversation($db, $user_id, $returned_session_id, $question, $bot_response);
    
    // Return response
    echo json_encode([
        'success' => true,
        'response' => $bot_response,
        'session_id' => $returned_session_id,
        'saved_to_db' => $saved,
        'user_logged_in' => isLoggedIn(),
        'fallback' => $chatbot_result['fallback'] ?? false,
        'smart' => $chatbot_result['smart'] ?? false
    ]);
    
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get conversation history
    $session_id = $_GET['session_id'] ?? null;
    
    if (!$session_id) {
        echo json_encode([
            'success' => false,
            'error' => 'Session ID is required'
        ]);
        exit;
    }
    
    $history = getConversationHistory($db, $session_id);
    
    echo json_encode([
        'success' => true,
        'history' => $history,
        'session_id' => $session_id
    ]);
    
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
}
?>
