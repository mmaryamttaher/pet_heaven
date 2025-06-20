<?php
require_once 'config/config.php';

echo "<h2>Setting Up App Ratings Database</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Check if app_ratings table exists
        $table_query = "SHOW TABLES LIKE 'app_ratings'";
        $table_stmt = $db->prepare($table_query);
        $table_stmt->execute();
        $table_exists = $table_stmt->fetch();
        
        if (!$table_exists) {
            // Create app_ratings table
            $create_table_sql = "
                CREATE TABLE app_ratings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
                    review TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_user_rating (user_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_rating (rating),
                    INDEX idx_created_at (created_at)
                )
            ";
            
            if ($db->exec($create_table_sql)) {
                echo "<p style='color: green;'>✓ App ratings table created successfully!</p>";
            } else {
                echo "<p style='color: red;'>✗ Error creating app ratings table</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ App ratings table already exists!</p>";
        }
        
        // Show table structure
        $structure_query = "DESCRIBE app_ratings";
        $structure_stmt = $db->prepare($structure_query);
        $structure_stmt->execute();
        $columns = $structure_stmt->fetchAll();
        
        echo "<h3>App Ratings Table Structure:</h3>";
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
        
        // Insert sample ratings if table is empty
        $count_query = "SELECT COUNT(*) as count FROM app_ratings";
        $count_stmt = $db->prepare($count_query);
        $count_stmt->execute();
        $total_ratings = $count_stmt->fetch()['count'];
        
        if ($total_ratings == 0) {
            echo "<p style='color: orange;'>⚠ No ratings found. Adding sample ratings...</p>";
            
            // Get some user IDs for sample data
            $users_query = "SELECT id FROM users LIMIT 5";
            $users_stmt = $db->prepare($users_query);
            $users_stmt->execute();
            $users = $users_stmt->fetchAll();
            
            if ($users) {
                $sample_ratings = [
                    ['rating' => 5, 'review' => 'Excellent app! Love the pet care features and easy booking system.'],
                    ['rating' => 4, 'review' => 'Great app overall. Very helpful for finding pet sitters in my area.'],
                    ['rating' => 5, 'review' => 'Amazing service! The app is user-friendly and the pet hosts are wonderful.'],
                    ['rating' => 3, 'review' => 'Good app but could use some improvements in the search functionality.'],
                    ['rating' => 4, 'review' => 'Really useful for pet owners. The booking process is smooth and reliable.']
                ];
                
                $insert_query = "INSERT INTO app_ratings (user_id, rating, review) VALUES (?, ?, ?)";
                $insert_stmt = $db->prepare($insert_query);
                
                foreach ($sample_ratings as $index => $sample) {
                    if (isset($users[$index])) {
                        $insert_stmt->execute([$users[$index]['id'], $sample['rating'], $sample['review']]);
                    }
                }
                
                echo "<p style='color: green;'>✓ Sample ratings added successfully!</p>";
            } else {
                echo "<p style='color: orange;'>⚠ No users found to create sample ratings</p>";
            }
        }
        
        // Show current ratings
        $ratings_query = "SELECT COUNT(*) as count FROM app_ratings";
        $ratings_stmt = $db->prepare($ratings_query);
        $ratings_stmt->execute();
        $total_ratings = $ratings_stmt->fetch()['count'];
        
        echo "<p><strong>Total app ratings in database:</strong> " . $total_ratings . "</p>";
        
        if ($total_ratings > 0) {
            // Show rating statistics
            $stats_query = "SELECT 
                            COUNT(*) as total_ratings,
                            AVG(rating) as average_rating,
                            MIN(rating) as min_rating,
                            MAX(rating) as max_rating
                            FROM app_ratings";
            $stats_stmt = $db->prepare($stats_query);
            $stats_stmt->execute();
            $stats = $stats_stmt->fetch();
            
            echo "<h3>Rating Statistics:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>Metric</th><th>Value</th>";
            echo "</tr>";
            echo "<tr><td>Total Ratings</td><td>" . $stats['total_ratings'] . "</td></tr>";
            echo "<tr><td>Average Rating</td><td>" . round($stats['average_rating'], 2) . " stars</td></tr>";
            echo "<tr><td>Highest Rating</td><td>" . $stats['max_rating'] . " stars</td></tr>";
            echo "<tr><td>Lowest Rating</td><td>" . $stats['min_rating'] . " stars</td></tr>";
            echo "</table>";
            
            // Show rating distribution
            $dist_query = "SELECT rating, COUNT(*) as count FROM app_ratings GROUP BY rating ORDER BY rating DESC";
            $dist_stmt = $db->prepare($dist_query);
            $dist_stmt->execute();
            $distribution = $dist_stmt->fetchAll();
            
            echo "<h3>Rating Distribution:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>Stars</th><th>Count</th><th>Percentage</th>";
            echo "</tr>";
            foreach ($distribution as $dist) {
                $percentage = round(($dist['count'] / $stats['total_ratings']) * 100, 1);
                echo "<tr>";
                echo "<td>" . $dist['rating'] . " ⭐</td>";
                echo "<td>" . $dist['count'] . "</td>";
                echo "<td>" . $percentage . "%</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Show recent ratings
            $recent_query = "SELECT ar.*, u.name as user_name 
                            FROM app_ratings ar 
                            JOIN users u ON ar.user_id = u.id 
                            ORDER BY ar.created_at DESC 
                            LIMIT 5";
            $recent_stmt = $db->prepare($recent_query);
            $recent_stmt->execute();
            $recent_ratings = $recent_stmt->fetchAll();
            
            if ($recent_ratings) {
                echo "<h3>Recent Ratings:</h3>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                echo "<tr style='background-color: #f0f0f0;'>";
                echo "<th>User</th><th>Rating</th><th>Review</th><th>Date</th>";
                echo "</tr>";
                foreach ($recent_ratings as $rating) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($rating['user_name']) . "</td>";
                    echo "<td>" . $rating['rating'] . " ⭐</td>";
                    echo "<td>" . htmlspecialchars(substr($rating['review'], 0, 50)) . "...</td>";
                    echo "<td>" . date('M j, Y', strtotime($rating['created_at'])) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ No app ratings found. Ratings will be created when users submit reviews.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Rating Features:</h3>";
echo "<ul>";
echo "<li><strong>5-Star Rating System:</strong> Users can rate from 1-5 stars using emoji interface</li>";
echo "<li><strong>Written Reviews:</strong> Optional text reviews up to 500 characters</li>";
echo "<li><strong>Update Capability:</strong> Users can update their existing ratings</li>";
echo "<li><strong>Rating Statistics:</strong> Display average rating and distribution</li>";
echo "<li><strong>User Isolation:</strong> One rating per user (unique constraint)</li>";
echo "<li><strong>Interactive UI:</strong> Clickable emoji interface with hover effects</li>";
echo "<li><strong>Validation:</strong> Client and server-side validation</li>";
echo "</ul>";

echo "<h3>Technical Features:</h3>";
echo "<ul>";
echo "<li><strong>Database Integration:</strong> Ratings stored in app_ratings table</li>";
echo "<li><strong>UPSERT Operations:</strong> Insert new or update existing ratings</li>";
echo "<li><strong>Statistics Dashboard:</strong> Real-time rating analytics</li>";
echo "<li><strong>User Authentication:</strong> Login required to submit ratings</li>";
echo "<li><strong>Responsive Design:</strong> Works on all devices</li>";
echo "<li><strong>Security:</strong> Input sanitization and SQL injection protection</li>";
echo "<li><strong>Performance:</strong> Indexed columns for fast queries</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='rate.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View Rating Page</a>";
echo "<a href='login.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Login</a>";
echo "<a href='user.php' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>User Profile</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
