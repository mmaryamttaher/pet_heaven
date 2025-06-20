<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Chatbot API</h2>";

try {
    require_once '../config/config.php';
    echo "<p style='color: green;'>✓ Config loaded successfully</p>";
    
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connected successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
    
    // Test simple chatbot
    echo "<h3>Testing Simple Chatbot:</h3>";
    require_once 'simple_chatbot.php';
    $simple_response = getEnhancedChatbotResponse("مرحبا");
    echo "<p><strong>Response:</strong> " . htmlspecialchars($simple_response) . "</p>";
    
    // Test smart chatbot
    echo "<h3>Testing Smart Chatbot:</h3>";
    require_once 'smart_chatbot.php';
    $smart_chatbot = new SmartChatbot($db);
    $smart_response = $smart_chatbot->getSmartResponse("ما هي المضيفين المتاحين؟", null);
    echo "<p><strong>Response:</strong> " . htmlspecialchars($smart_response) . "</p>";
    
    // Test API call
    echo "<h3>Testing API Call:</h3>";
    $test_question = "مرحبا";
    $test_data = json_encode(['question' => $test_question]);
    
    $url = 'http://localhost/pets_shop/chatbot_api.php';
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => $test_data,
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result !== FALSE) {
        $response = json_decode($result, true);
        echo "<p style='color: green;'>✓ API call successful</p>";
        echo "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ API call failed</p>";
        echo "<p>Error: " . error_get_last()['message'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<h3>PHP Error Log:</h3>";
$error_log = error_get_last();
if ($error_log) {
    echo "<pre>" . print_r($error_log, true) . "</pre>";
} else {
    echo "<p>No PHP errors found</p>";
}
?>
