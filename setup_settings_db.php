<?php
require_once 'config/config.php';

echo "<h2>Setting Up User Settings Database</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Check if user_settings table exists
        $table_query = "SHOW TABLES LIKE 'user_settings'";
        $table_stmt = $db->prepare($table_query);
        $table_stmt->execute();
        $table_exists = $table_stmt->fetch();
        
        if (!$table_exists) {
            // Create user_settings table
            $create_table_sql = "
                CREATE TABLE user_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    language VARCHAR(20) DEFAULT 'English',
                    currency VARCHAR(10) DEFAULT 'EGP',
                    country VARCHAR(50) DEFAULT 'Egypt',
                    notifications VARCHAR(10) DEFAULT 'Enable',
                    theme VARCHAR(20) DEFAULT 'light',
                    timezone VARCHAR(50) DEFAULT 'Africa/Cairo',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_user (user_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ";
            
            if ($db->exec($create_table_sql)) {
                echo "<p style='color: green;'>✓ User settings table created successfully!</p>";
            } else {
                echo "<p style='color: red;'>✗ Error creating user settings table</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ User settings table already exists!</p>";
        }
        
        // Show table structure
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
        
        // Check current settings
        $settings_query = "SELECT COUNT(*) as count FROM user_settings";
        $settings_stmt = $db->prepare($settings_query);
        $settings_stmt->execute();
        $total_settings = $settings_stmt->fetch()['count'];
        
        echo "<p><strong>Total user settings in database:</strong> " . $total_settings . "</p>";
        
        if ($total_settings > 0) {
            // Show sample settings
            $sample_settings_query = "SELECT us.*, u.name as user_name FROM user_settings us JOIN users u ON us.user_id = u.id LIMIT 5";
            $sample_settings_stmt = $db->prepare($sample_settings_query);
            $sample_settings_stmt->execute();
            $sample_settings = $sample_settings_stmt->fetchAll();
            
            echo "<h3>Sample User Settings:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>User</th><th>Language</th><th>Currency</th><th>Country</th><th>Notifications</th><th>Updated</th>";
            echo "</tr>";
            foreach ($sample_settings as $setting) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($setting['user_name']) . "</td>";
                echo "<td>" . $setting['language'] . "</td>";
                echo "<td>" . $setting['currency'] . "</td>";
                echo "<td>" . $setting['country'] . "</td>";
                echo "<td>" . $setting['notifications'] . "</td>";
                echo "<td>" . $setting['updated_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Show settings statistics
            echo "<h3>Settings Statistics:</h3>";
            
            $stats_queries = [
                'Language' => "SELECT language, COUNT(*) as count FROM user_settings GROUP BY language",
                'Currency' => "SELECT currency, COUNT(*) as count FROM user_settings GROUP BY currency",
                'Country' => "SELECT country, COUNT(*) as count FROM user_settings GROUP BY country",
                'Notifications' => "SELECT notifications, COUNT(*) as count FROM user_settings GROUP BY notifications"
            ];
            
            foreach ($stats_queries as $stat_name => $query) {
                $stat_stmt = $db->prepare($query);
                $stat_stmt->execute();
                $stats = $stat_stmt->fetchAll();
                
                if ($stats) {
                    echo "<h4>" . $stat_name . " Distribution:</h4>";
                    echo "<ul>";
                    foreach ($stats as $stat) {
                        $key = strtolower($stat_name);
                        echo "<li><strong>" . $stat[$key] . ":</strong> " . $stat['count'] . " users</li>";
                    }
                    echo "</ul>";
                }
            }
        } else {
            echo "<p style='color: orange;'>⚠ No user settings found. Settings will be created when users update their preferences.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Settings Features:</h3>";
echo "<ul>";
echo "<li><strong>Language Selection:</strong> Arabic, English</li>";
echo "<li><strong>Currency Options:</strong> EGP, SAR, CHF</li>";
echo "<li><strong>Country Selection:</strong> Egypt, Saudi Arabia, Switzerland</li>";
echo "<li><strong>Notification Control:</strong> Enable/Disable notifications</li>";
echo "<li><strong>Persistent Storage:</strong> Settings saved to database</li>";
echo "<li><strong>User Isolation:</strong> Each user has their own settings</li>";
echo "<li><strong>Default Values:</strong> Sensible defaults for new users</li>";
echo "</ul>";

echo "<h3>Technical Features:</h3>";
echo "<ul>";
echo "<li><strong>Database Integration:</strong> Settings stored in user_settings table</li>";
echo "<li><strong>Form Validation:</strong> Radio button selections with current values</li>";
echo "<li><strong>Success Messages:</strong> User feedback on settings updates</li>";
echo "<li><strong>Logout Functionality:</strong> Secure session termination</li>";
echo "<li><strong>Responsive Design:</strong> Works on all devices</li>";
echo "<li><strong>Security:</strong> Login required, input sanitization</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='setting.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View Settings Page</a>";
echo "<a href='login.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Login</a>";
echo "<a href='user.php' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>User Profile</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
