<?php
require_once 'config/config.php';

echo "<h2>Setting Up Chatbot Database</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Check if chat_conversations table exists
        $table_query = "SHOW TABLES LIKE 'chat_conversations'";
        $table_stmt = $db->prepare($table_query);
        $table_stmt->execute();
        $table_exists = $table_stmt->fetch();
        
        if (!$table_exists) {
            // Create chat_conversations table
            $create_table_sql = "
                CREATE TABLE chat_conversations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    session_id VARCHAR(100) NOT NULL,
                    user_message TEXT NOT NULL,
                    bot_response TEXT NOT NULL,
                    message_type ENUM('question', 'answer') DEFAULT 'question',
                    language VARCHAR(10) DEFAULT 'ar',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_session_id (session_id),
                    INDEX idx_user_id (user_id),
                    INDEX idx_created_at (created_at)
                )
            ";
            
            if ($db->exec($create_table_sql)) {
                echo "<p style='color: green;'>✓ Chat conversations table created successfully!</p>";
            } else {
                echo "<p style='color: red;'>✗ Error creating chat conversations table</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ Chat conversations table already exists!</p>";
        }
        
        // Check if chat_sessions table exists
        $sessions_query = "SHOW TABLES LIKE 'chat_sessions'";
        $sessions_stmt = $db->prepare($sessions_query);
        $sessions_stmt->execute();
        $sessions_exists = $sessions_stmt->fetch();
        
        if (!$sessions_exists) {
            // Create chat_sessions table
            $create_sessions_sql = "
                CREATE TABLE chat_sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    session_id VARCHAR(100) NOT NULL UNIQUE,
                    user_id INT,
                    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    is_active BOOLEAN DEFAULT TRUE,
                    total_messages INT DEFAULT 0,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_session_id (session_id),
                    INDEX idx_user_id (user_id)
                )
            ";
            
            if ($db->exec($create_sessions_sql)) {
                echo "<p style='color: green;'>✓ Chat sessions table created successfully!</p>";
            } else {
                echo "<p style='color: red;'>✗ Error creating chat sessions table</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ Chat sessions table already exists!</p>";
        }
        
        // Show table structures
        echo "<h3>Chat Conversations Table Structure:</h3>";
        $structure_query = "DESCRIBE chat_conversations";
        $structure_stmt = $db->prepare($structure_query);
        $structure_stmt->execute();
        $columns = $structure_stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th>";
        echo "</tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Insert sample conversation if table is empty
        $count_query = "SELECT COUNT(*) as count FROM chat_conversations";
        $count_stmt = $db->prepare($count_query);
        $count_stmt->execute();
        $total_conversations = $count_stmt->fetch()['count'];
        
        if ($total_conversations == 0) {
            echo "<p style='color: orange;'>⚠ No conversations found. Adding sample conversation...</p>";
            
            // Get a user ID for sample data
            $users_query = "SELECT id FROM users LIMIT 1";
            $users_stmt = $db->prepare($users_query);
            $users_stmt->execute();
            $user = $users_stmt->fetch();
            
            if ($user) {
                $sample_session_id = 'session_' . date('YmdHis');
                
                // Insert sample session
                $insert_session = "INSERT INTO chat_sessions (session_id, user_id, total_messages) VALUES (?, ?, ?)";
                $session_stmt = $db->prepare($insert_session);
                $session_stmt->execute([$sample_session_id, $user['id'], 2]);
                
                // Insert sample conversations
                $sample_conversations = [
                    [
                        'user_id' => $user['id'],
                        'session_id' => $sample_session_id,
                        'user_message' => 'ما هي أفضل طريقة لرعاية القطط؟',
                        'bot_response' => 'رعاية القطط تتطلب عدة أمور مهمة: 1) توفير طعام صحي ومتوازن 2) الماء النظيف دائماً 3) صندوق رمل نظيف 4) زيارات دورية للطبيب البيطري 5) اللعب والتفاعل اليومي 6) مكان آمن ومريح للنوم. هل تريد معرفة المزيد عن أي من هذه النقاط؟'
                    ],
                    [
                        'user_id' => $user['id'],
                        'session_id' => $sample_session_id,
                        'user_message' => 'كم مرة يجب أن أطعم قطتي في اليوم؟',
                        'bot_response' => 'يعتمد عدد وجبات القطط على عمرها: القطط الصغيرة (أقل من 6 أشهر) تحتاج 3-4 وجبات يومياً، القطط البالغة (6 أشهر - 7 سنوات) تحتاج وجبتين يومياً، القطط الكبيرة (أكثر من 7 سنوات) قد تحتاج وجبات أصغر وأكثر تكراراً. المهم هو الحفاظ على كمية ثابتة من الطعام حسب وزن القطة ونشاطها.'
                    ]
                ];
                
                $insert_query = "INSERT INTO chat_conversations (user_id, session_id, user_message, bot_response) VALUES (?, ?, ?, ?)";
                $insert_stmt = $db->prepare($insert_query);
                
                foreach ($sample_conversations as $conversation) {
                    $insert_stmt->execute([
                        $conversation['user_id'],
                        $conversation['session_id'],
                        $conversation['user_message'],
                        $conversation['bot_response']
                    ]);
                }
                
                echo "<p style='color: green;'>✓ Sample conversations added successfully!</p>";
            } else {
                echo "<p style='color: orange;'>⚠ No users found to create sample conversations</p>";
            }
        }
        
        // Show current statistics
        $conversations_query = "SELECT COUNT(*) as count FROM chat_conversations";
        $conversations_stmt = $db->prepare($conversations_query);
        $conversations_stmt->execute();
        $total_conversations = $conversations_stmt->fetch()['count'];
        
        echo "<p><strong>Total conversations in database:</strong> " . $total_conversations . "</p>";
        
        if ($total_conversations > 0) {
            // Show conversation statistics
            $stats_query = "SELECT 
                            COUNT(DISTINCT session_id) as total_sessions,
                            COUNT(DISTINCT user_id) as unique_users,
                            AVG(LENGTH(user_message)) as avg_question_length,
                            AVG(LENGTH(bot_response)) as avg_response_length
                            FROM chat_conversations";
            $stats_stmt = $db->prepare($stats_query);
            $stats_stmt->execute();
            $stats = $stats_stmt->fetch();
            
            echo "<h3>Chat Statistics:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>Metric</th><th>Value</th>";
            echo "</tr>";
            echo "<tr><td>Total Sessions</td><td>" . $stats['total_sessions'] . "</td></tr>";
            echo "<tr><td>Unique Users</td><td>" . $stats['unique_users'] . "</td></tr>";
            echo "<tr><td>Average Question Length</td><td>" . round($stats['avg_question_length']) . " characters</td></tr>";
            echo "<tr><td>Average Response Length</td><td>" . round($stats['avg_response_length']) . " characters</td></tr>";
            echo "</table>";
            
            // Show recent conversations
            $recent_query = "SELECT cc.*, u.name as user_name 
                            FROM chat_conversations cc 
                            LEFT JOIN users u ON cc.user_id = u.id 
                            ORDER BY cc.created_at DESC 
                            LIMIT 5";
            $recent_stmt = $db->prepare($recent_query);
            $recent_stmt->execute();
            $recent_conversations = $recent_stmt->fetchAll();
            
            if ($recent_conversations) {
                echo "<h3>Recent Conversations:</h3>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                echo "<tr style='background-color: #f0f0f0;'>";
                echo "<th>User</th><th>Question</th><th>Response</th><th>Date</th>";
                echo "</tr>";
                foreach ($recent_conversations as $conversation) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($conversation['user_name'] ?? 'Anonymous') . "</td>";
                    echo "<td>" . htmlspecialchars(substr($conversation['user_message'], 0, 50)) . "...</td>";
                    echo "<td>" . htmlspecialchars(substr($conversation['bot_response'], 0, 50)) . "...</td>";
                    echo "<td>" . date('M j, Y H:i', strtotime($conversation['created_at'])) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Chatbot Features:</h3>";
echo "<ul>";
echo "<li><strong>Conversation Storage:</strong> All chat messages saved to database</li>";
echo "<li><strong>Session Management:</strong> Track chat sessions and user activity</li>";
echo "<li><strong>User Integration:</strong> Link conversations to registered users</li>";
echo "<li><strong>Anonymous Support:</strong> Allow anonymous users to chat</li>";
echo "<li><strong>Message History:</strong> Retrieve previous conversations</li>";
echo "<li><strong>Analytics:</strong> Track chat usage and statistics</li>";
echo "<li><strong>Multi-language:</strong> Support for Arabic and English</li>";
echo "</ul>";

echo "<h3>Technical Integration:</h3>";
echo "<ul>";
echo "<li><strong>Python FastAPI:</strong> Backend chatbot service</li>";
echo "<li><strong>PHP Frontend:</strong> Web interface for chat</li>";
echo "<li><strong>Database Storage:</strong> MySQL for conversation persistence</li>";
echo "<li><strong>Session Management:</strong> Track user chat sessions</li>";
echo "<li><strong>API Integration:</strong> PHP calls Python chatbot API</li>";
echo "<li><strong>Real-time Chat:</strong> AJAX for smooth chat experience</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='chat.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View Chat Page</a>";
echo "<a href='test_chatbot_connection.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Test Chatbot</a>";
echo "<a href='login.php' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>Login</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
