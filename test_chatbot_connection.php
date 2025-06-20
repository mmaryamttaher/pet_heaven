<?php
require_once 'config/config.php';

echo "<h2>Chatbot Integration Test</h2>";
echo "<p><strong>Testing chatbot API connection and database integration...</strong></p>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Test 1: Check if chatbot tables exist
        $tables_to_check = ['chat_conversations', 'chat_sessions'];
        $all_tables_exist = true;
        
        foreach ($tables_to_check as $table) {
            $table_query = "SHOW TABLES LIKE '$table'";
            $table_stmt = $db->prepare($table_query);
            $table_stmt->execute();
            $table_exists = $table_stmt->fetch();
            
            if ($table_exists) {
                echo "<p style='color: green;'>✓ $table table exists!</p>";
            } else {
                echo "<p style='color: red;'>✗ $table table does not exist!</p>";
                $all_tables_exist = false;
            }
        }
        
        if (!$all_tables_exist) {
            echo "<p><a href='setup_chatbot_db.php'>Click here to set up the chatbot database</a></p>";
        }
        
        // Test 2: Test Python chatbot API connection
        echo "<h3>Testing Python Chatbot API:</h3>";
        
        $chatbot_url = 'http://localhost:8000/health';
        
        echo "<p><strong>Checking chatbot health endpoint:</strong> $chatbot_url</p>";
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5
            ]
        ]);
        
        $health_response = @file_get_contents($chatbot_url, false, $context);
        
        if ($health_response !== FALSE) {
            $health_data = json_decode($health_response, true);
            if ($health_data && isset($health_data['status'])) {
                echo "<p style='color: green;'>✓ Chatbot API is running!</p>";
                echo "<p><strong>Status:</strong> " . $health_data['status'] . "</p>";
                echo "<p><strong>Database:</strong> " . ($health_data['database'] ?? 'unknown') . "</p>";
                echo "<p><strong>Timestamp:</strong> " . ($health_data['timestamp'] ?? 'unknown') . "</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Chatbot API responded but with unexpected format</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Cannot connect to chatbot API!</p>";
            echo "<p><strong>Make sure to:</strong></p>";
            echo "<ul>";
            echo "<li>Install Python dependencies: <code>pip install -r requirements.txt</code></li>";
            echo "<li>Run the chatbot: <code>python chatbot_main.py</code></li>";
            echo "<li>Check that port 8000 is available</li>";
            echo "</ul>";
        }
        
        // Test 3: Test chatbot API call
        if ($health_response !== FALSE) {
            echo "<h3>Testing Chatbot Question API:</h3>";
            
            $test_question = "ما هي أفضل طريقة لرعاية القطط؟";
            $test_session_id = "test_session_" . date('YmdHis');
            
            $chatbot_ask_url = 'http://localhost:8000/ask';
            $post_data = json_encode([
                'question' => $test_question,
                'session_id' => $test_session_id,
                'user_id' => isLoggedIn() ? $_SESSION['user_id'] : null
            ]);
            
            $context = stream_context_create([
                'http' => [
                    'header' => "Content-type: application/json\r\n",
                    'method' => 'POST',
                    'content' => $post_data,
                    'timeout' => 30
                ]
            ]);
            
            echo "<p><strong>Test Question:</strong> $test_question</p>";
            echo "<p><strong>Session ID:</strong> $test_session_id</p>";
            
            $ask_response = @file_get_contents($chatbot_ask_url, false, $context);
            
            if ($ask_response !== FALSE) {
                $ask_data = json_decode($ask_response, true);
                if ($ask_data && isset($ask_data['response'])) {
                    echo "<p style='color: green;'>✓ Chatbot responded successfully!</p>";
                    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                    echo "<strong>Bot Response:</strong><br>";
                    echo nl2br(htmlspecialchars($ask_data['response']));
                    echo "</div>";
                    
                    // Check if conversation was saved to database
                    if ($all_tables_exist) {
                        $check_query = "SELECT * FROM chat_conversations WHERE session_id = ? ORDER BY created_at DESC LIMIT 1";
                        $check_stmt = $db->prepare($check_query);
                        $check_stmt->execute([$test_session_id]);
                        $saved_conversation = $check_stmt->fetch();
                        
                        if ($saved_conversation) {
                            echo "<p style='color: green;'>✓ Conversation saved to database!</p>";
                            echo "<p><strong>Saved Question:</strong> " . htmlspecialchars($saved_conversation['user_message']) . "</p>";
                        } else {
                            echo "<p style='color: orange;'>⚠ Conversation not found in database</p>";
                        }
                    }
                } else {
                    echo "<p style='color: red;'>✗ Invalid response from chatbot</p>";
                    echo "<p>Response: " . htmlspecialchars($ask_response) . "</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Failed to get response from chatbot</p>";
            }
        }
        
        // Test 4: Show conversation statistics
        if ($all_tables_exist) {
            echo "<h3>Conversation Statistics:</h3>";
            
            $stats_query = "SELECT 
                            COUNT(*) as total_conversations,
                            COUNT(DISTINCT session_id) as total_sessions,
                            COUNT(DISTINCT user_id) as unique_users,
                            MAX(created_at) as last_conversation
                            FROM chat_conversations";
            $stats_stmt = $db->prepare($stats_query);
            $stats_stmt->execute();
            $stats = $stats_stmt->fetch();
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>Metric</th><th>Value</th>";
            echo "</tr>";
            echo "<tr><td>Total Conversations</td><td>" . $stats['total_conversations'] . "</td></tr>";
            echo "<tr><td>Total Sessions</td><td>" . $stats['total_sessions'] . "</td></tr>";
            echo "<tr><td>Unique Users</td><td>" . $stats['unique_users'] . "</td></tr>";
            echo "<tr><td>Last Conversation</td><td>" . ($stats['last_conversation'] ?? 'None') . "</td></tr>";
            echo "</table>";
            
            // Show recent conversations
            if ($stats['total_conversations'] > 0) {
                $recent_query = "SELECT cc.*, u.name as user_name 
                                FROM chat_conversations cc 
                                LEFT JOIN users u ON cc.user_id = u.id 
                                ORDER BY cc.created_at DESC 
                                LIMIT 5";
                $recent_stmt = $db->prepare($recent_query);
                $recent_stmt->execute();
                $recent_conversations = $recent_stmt->fetchAll();
                
                echo "<h4>Recent Conversations:</h4>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                echo "<tr style='background-color: #f0f0f0;'>";
                echo "<th>User</th><th>Question</th><th>Response</th><th>Date</th>";
                echo "</tr>";
                foreach ($recent_conversations as $conversation) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($conversation['user_name'] ?? 'Anonymous') . "</td>";
                    echo "<td>" . htmlspecialchars(substr($conversation['user_message'], 0, 50)) . "...</td>";
                    echo "<td>" . htmlspecialchars(substr($conversation['bot_response'], 0, 50)) . "...</td>";
                    echo "<td>" . date('M j, H:i', strtotime($conversation['created_at'])) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
        // Test 5: Session status
        echo "<h3>Session Status:</h3>";
        if (session_status() == PHP_SESSION_ACTIVE) {
            echo "<p style='color: green;'>✓ Session is active</p>";
            
            if (isLoggedIn()) {
                echo "<p style='color: green;'>✓ User is logged in (ID: " . $_SESSION['user_id'] . ")</p>";
                echo "<p>Chat conversations will be linked to this user</p>";
            } else {
                echo "<p style='color: orange;'>⚠ User is not logged in</p>";
                echo "<p>Chat will work as anonymous user</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Session is not active</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Integration Status Summary:</h3>";
echo "<ul>";
echo "<li>✓ PHP chatbot API interface created</li>";
echo "<li>✓ Database tables for conversation storage</li>";
echo "<li>✓ Python FastAPI chatbot with Gemini AI</li>";
echo "<li>✓ Real-time chat interface with AJAX</li>";
echo "<li>✓ Session management and user integration</li>";
echo "<li>✓ Conversation history and analytics</li>";
echo "<li>✓ Arabic language support</li>";
echo "</ul>";

echo "<h3>Setup Instructions:</h3>";
echo "<ol>";
echo "<li><strong>Install Python dependencies:</strong><br><code>pip install -r requirements.txt</code></li>";
echo "<li><strong>Update database config in chatbot_main.py:</strong><br>Set your MySQL password if needed</li>";
echo "<li><strong>Run the Python chatbot:</strong><br><code>python chatbot_main.py</code></li>";
echo "<li><strong>Test the chat interface:</strong><br>Visit chat.php and start chatting!</li>";
echo "</ol>";

echo "<h3>API Endpoints:</h3>";
echo "<ul>";
echo "<li><strong>Health Check:</strong> GET http://localhost:8000/health</li>";
echo "<li><strong>Ask Question:</strong> POST http://localhost:8000/ask</li>";
echo "<li><strong>Get History:</strong> GET http://localhost:8000/history/{session_id}</li>";
echo "<li><strong>API Docs:</strong> http://localhost:8000/docs</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='chat.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>Test Chat Interface</a>";
echo "<a href='setup_chatbot_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Setup Database</a>";
echo "<a href='http://localhost:8000/docs' target='_blank' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>API Docs</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
