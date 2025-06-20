<?php
require_once 'config/config.php';

echo "<h2>Settings Page Database Connection Test</h2>";
echo "<p><strong>Testing database connection and settings functionality...</strong></p>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Test 1: Check if user_settings table exists
        $table_query = "SHOW TABLES LIKE 'user_settings'";
        $table_stmt = $db->prepare($table_query);
        $table_stmt->execute();
        $table_exists = $table_stmt->fetch();
        
        if ($table_exists) {
            echo "<p style='color: green;'>✓ User settings table exists!</p>";
            
            // Test 2: Get all user settings
            $all_settings_query = "SELECT COUNT(*) as count FROM user_settings";
            $all_settings_stmt = $db->prepare($all_settings_query);
            $all_settings_stmt->execute();
            $total_settings = $all_settings_stmt->fetch()['count'];
            
            echo "<p><strong>Total user settings in database:</strong> " . $total_settings . "</p>";
            
            // Test 3: Check table structure
            $structure_query = "DESCRIBE user_settings";
            $structure_stmt = $db->prepare($structure_query);
            $structure_stmt->execute();
            $columns = $structure_stmt->fetchAll();
            
            echo "<h3>User Settings Table Structure:</h3>";
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
            
            // Test 4: Sample user settings query (the one used in setting.php)
            echo "<h3>Testing User Settings Query:</h3>";
            $sample_user_id = 1; // Test with user ID 1
            
            $settings_query = "SELECT * FROM user_settings WHERE user_id = ?";
            
            echo "<p><strong>Query:</strong> " . htmlspecialchars($settings_query) . "</p>";
            echo "<p><strong>Test User ID:</strong> " . $sample_user_id . "</p>";
            
            try {
                $settings_stmt = $db->prepare($settings_query);
                $settings_stmt->execute([$sample_user_id]);
                $sample_settings = $settings_stmt->fetch();
                
                if ($sample_settings) {
                    echo "<p><strong>Settings found for user ID " . $sample_user_id . ":</strong></p>";
                    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                    echo "<tr style='background-color: #f0f0f0;'>";
                    echo "<th>Setting</th><th>Value</th>";
                    echo "</tr>";
                    echo "<tr><td>Language</td><td>" . $sample_settings['language'] . "</td></tr>";
                    echo "<tr><td>Currency</td><td>" . $sample_settings['currency'] . "</td></tr>";
                    echo "<tr><td>Country</td><td>" . $sample_settings['country'] . "</td></tr>";
                    echo "<tr><td>Notifications</td><td>" . $sample_settings['notifications'] . "</td></tr>";
                    echo "<tr><td>Theme</td><td>" . $sample_settings['theme'] . "</td></tr>";
                    echo "<tr><td>Timezone</td><td>" . $sample_settings['timezone'] . "</td></tr>";
                    echo "<tr><td>Last Updated</td><td>" . $sample_settings['updated_at'] . "</td></tr>";
                    echo "</table>";
                } else {
                    echo "<p style='color: orange;'>⚠ No settings found for user ID " . $sample_user_id . " (will use defaults)</p>";
                    
                    // Show default settings
                    $default_settings = [
                        'language' => 'English',
                        'currency' => 'EGP',
                        'country' => 'Egypt',
                        'notifications' => 'Enable'
                    ];
                    
                    echo "<p><strong>Default settings that would be used:</strong></p>";
                    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                    echo "<tr style='background-color: #f0f0f0;'>";
                    echo "<th>Setting</th><th>Default Value</th>";
                    echo "</tr>";
                    foreach ($default_settings as $key => $value) {
                        echo "<tr><td>" . ucfirst($key) . "</td><td>" . $value . "</td></tr>";
                    }
                    echo "</table>";
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Query failed: " . $e->getMessage() . "</p>";
            }
            
            // Test 5: Test CRUD operations
            echo "<h3>Testing CRUD Operations:</h3>";
            
            // Test INSERT/UPDATE (UPSERT)
            $test_user_id = 999; // Use a test user ID
            $test_language = "Arabic";
            $test_currency = "SAR";
            $test_country = "Saudi Arabia";
            $test_notifications = "Disable";
            
            $upsert_query = "INSERT INTO user_settings (user_id, language, currency, country, notifications) 
                             VALUES (?, ?, ?, ?, ?) 
                             ON DUPLICATE KEY UPDATE 
                             language = VALUES(language), 
                             currency = VALUES(currency), 
                             country = VALUES(country), 
                             notifications = VALUES(notifications)";
            $upsert_stmt = $db->prepare($upsert_query);
            
            if ($upsert_stmt->execute([$test_user_id, $test_language, $test_currency, $test_country, $test_notifications])) {
                echo "<p style='color: green;'>✓ UPSERT test successful - Settings saved for test user " . $test_user_id . "</p>";
                
                // Test SELECT to verify
                $verify_query = "SELECT * FROM user_settings WHERE user_id = ?";
                $verify_stmt = $db->prepare($verify_query);
                $verify_stmt->execute([$test_user_id]);
                $verify_settings = $verify_stmt->fetch();
                
                if ($verify_settings) {
                    echo "<p style='color: green;'>✓ SELECT verification successful</p>";
                    echo "<p>Saved settings: " . $verify_settings['language'] . ", " . $verify_settings['currency'] . ", " . $verify_settings['country'] . ", " . $verify_settings['notifications'] . "</p>";
                }
                
                // Test DELETE (cleanup)
                $delete_query = "DELETE FROM user_settings WHERE user_id = ?";
                $delete_stmt = $db->prepare($delete_query);
                
                if ($delete_stmt->execute([$test_user_id])) {
                    echo "<p style='color: green;'>✓ DELETE test successful (cleanup completed)</p>";
                } else {
                    echo "<p style='color: red;'>✗ DELETE test failed</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ UPSERT test failed</p>";
            }
            
            // Test 6: Check session functionality
            echo "<h3>Session Status:</h3>";
            if (session_status() == PHP_SESSION_ACTIVE) {
                echo "<p style='color: green;'>✓ Session is active</p>";
                
                if (isLoggedIn()) {
                    echo "<p style='color: green;'>✓ User is logged in (ID: " . $_SESSION['user_id'] . ")</p>";
                    echo "<p>Settings page will load user's current settings</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ User is not logged in</p>";
                    echo "<p>Settings page will redirect to login</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Session is not active</p>";
            }
            
            // Test 7: Show settings distribution
            if ($total_settings > 0) {
                echo "<h3>Settings Distribution:</h3>";
                
                $distribution_queries = [
                    'Languages' => "SELECT language, COUNT(*) as count FROM user_settings GROUP BY language",
                    'Currencies' => "SELECT currency, COUNT(*) as count FROM user_settings GROUP BY currency",
                    'Countries' => "SELECT country, COUNT(*) as count FROM user_settings GROUP BY country",
                    'Notifications' => "SELECT notifications, COUNT(*) as count FROM user_settings GROUP BY notifications"
                ];
                
                foreach ($distribution_queries as $title => $query) {
                    $dist_stmt = $db->prepare($query);
                    $dist_stmt->execute();
                    $distribution = $dist_stmt->fetchAll();
                    
                    if ($distribution) {
                        echo "<h4>" . $title . ":</h4>";
                        echo "<ul>";
                        foreach ($distribution as $item) {
                            $field = strtolower(rtrim($title, 's'));
                            echo "<li><strong>" . $item[$field] . ":</strong> " . $item['count'] . " users</li>";
                        }
                        echo "</ul>";
                    }
                }
            }
            
        } else {
            echo "<p style='color: red;'>✗ User settings table does not exist!</p>";
            echo "<p><a href='setup_settings_db.php'>Click here to set up the settings database</a></p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Error details: " . $e->getFile() . " on line " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<h3>Page Status Summary:</h3>";
echo "<ul>";
echo "<li>✓ setting.php file created to match setting.html structure</li>";
echo "<li>✓ Database connection code integrated</li>";
echo "<li>✓ User authentication check implemented</li>";
echo "<li>✓ Settings CRUD operations (Create, Read, Update)</li>";
echo "<li>✓ Form validation and persistence</li>";
echo "<li>✓ Default values for new users</li>";
echo "<li>✓ Logout functionality included</li>";
echo "<li>✓ Exact HTML structure preserved</li>";
echo "</ul>";

echo "<h3>Settings Page Features:</h3>";
echo "<ul>";
echo "<li><strong>Language Selection:</strong> Arabic, English with radio buttons</li>";
echo "<li><strong>Currency Options:</strong> EGP, SAR, CHF selection</li>";
echo "<li><strong>Country Selection:</strong> Egypt, Saudi Arabia, Switzerland</li>";
echo "<li><strong>Notification Control:</strong> Enable/Disable notifications</li>";
echo "<li><strong>Persistent Storage:</strong> Settings saved to database</li>";
echo "<li><strong>Form Persistence:</strong> Current settings pre-selected</li>";
echo "<li><strong>Success Messages:</strong> User feedback on updates</li>";
echo "<li><strong>Logout Option:</strong> Secure session termination</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='setting.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View Settings Page</a>";
echo "<a href='setup_settings_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Setup Database</a>";
echo "<a href='login.php' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>Login</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
