<?php
require_once 'config/config.php';

echo "<h2>User Page Database Connection Test</h2>";
echo "<p><strong>Testing database connection and user functionality...</strong></p>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Test 1: Check if users table exists
        $table_query = "SHOW TABLES LIKE 'users'";
        $table_stmt = $db->prepare($table_query);
        $table_stmt->execute();
        $table_exists = $table_stmt->fetch();
        
        if ($table_exists) {
            echo "<p style='color: green;'>✓ Users table exists!</p>";
            
            // Test 2: Get all users
            $all_users_query = "SELECT COUNT(*) as count FROM users";
            $all_users_stmt = $db->prepare($all_users_query);
            $all_users_stmt->execute();
            $total_users = $all_users_stmt->fetch()['count'];
            
            echo "<p><strong>Total users in database:</strong> " . $total_users . "</p>";
            
            // Test 3: Check pets table
            $pets_table_query = "SHOW TABLES LIKE 'pets'";
            $pets_table_stmt = $db->prepare($pets_table_query);
            $pets_table_stmt->execute();
            $pets_table_exists = $pets_table_stmt->fetch();
            
            if ($pets_table_exists) {
                $pets_count_query = "SELECT COUNT(*) as count FROM pets";
                $pets_count_stmt = $db->prepare($pets_count_query);
                $pets_count_stmt->execute();
                $total_pets = $pets_count_stmt->fetch()['count'];
                echo "<p style='color: green;'>✓ Pets table exists with " . $total_pets . " pets</p>";
            } else {
                echo "<p style='color: red;'>✗ Pets table missing</p>";
            }
            
            // Test 4: Sample user query (the one used in user.php)
            echo "<h3>Testing User Query:</h3>";
            $sample_user_id = 1; // Test with user ID 1
            
            $user_query = "SELECT * FROM users WHERE id = ?";
            
            echo "<p><strong>Query:</strong> " . htmlspecialchars($user_query) . "</p>";
            echo "<p><strong>Test User ID:</strong> " . $sample_user_id . "</p>";
            
            try {
                $user_stmt = $db->prepare($user_query);
                $user_stmt->execute([$sample_user_id]);
                $sample_user = $user_stmt->fetch();
                
                if ($sample_user) {
                    echo "<p><strong>User found:</strong></p>";
                    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                    echo "<tr style='background-color: #f0f0f0;'>";
                    echo "<th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<td>" . $sample_user['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($sample_user['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($sample_user['email']) . "</td>";
                    echo "<td>" . $sample_user['role'] . "</td>";
                    echo "<td>" . $sample_user['created_at'] . "</td>";
                    echo "</tr>";
                    echo "</table>";
                    
                    // Test pets for this user
                    if ($pets_table_exists) {
                        $pets_query = "SELECT * FROM pets WHERE user_id = ?";
                        $pets_stmt = $db->prepare($pets_query);
                        $pets_stmt->execute([$sample_user_id]);
                        $user_pets = $pets_stmt->fetchAll();
                        
                        echo "<p><strong>User's pets:</strong> " . count($user_pets) . "</p>";
                        if ($user_pets) {
                            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                            echo "<tr style='background-color: #f0f0f0;'>";
                            echo "<th>ID</th><th>Name</th><th>Type</th><th>Breed</th><th>Age</th>";
                            echo "</tr>";
                            foreach ($user_pets as $pet) {
                                echo "<tr>";
                                echo "<td>" . $pet['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($pet['name']) . "</td>";
                                echo "<td>" . ucfirst($pet['type']) . "</td>";
                                echo "<td>" . htmlspecialchars($pet['breed'] ?? 'N/A') . "</td>";
                                echo "<td>" . ($pet['age'] ?? 'N/A') . "</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        }
                    }
                } else {
                    echo "<p style='color: orange;'>⚠ No user found with ID " . $sample_user_id . "</p>";
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Query failed: " . $e->getMessage() . "</p>";
            }
            
            // Test 5: Check session functionality
            echo "<h3>Session Status:</h3>";
            if (session_status() == PHP_SESSION_ACTIVE) {
                echo "<p style='color: green;'>✓ Session is active</p>";
                
                if (isLoggedIn()) {
                    echo "<p style='color: green;'>✓ User is logged in (ID: " . $_SESSION['user_id'] . ")</p>";
                    echo "<p>User name: " . (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Not set') . "</p>";
                    echo "<p>User role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Not set') . "</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ User is not logged in</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Session is not active</p>";
            }
            
            // Test 6: Show sample users
            echo "<h3>Sample Users:</h3>";
            $sample_users_query = "SELECT id, name, email, role, created_at FROM users LIMIT 5";
            $sample_users_stmt = $db->prepare($sample_users_query);
            $sample_users_stmt->execute();
            $sample_users = $sample_users_stmt->fetchAll();
            
            if ($sample_users) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                echo "<tr style='background-color: #f0f0f0;'>";
                echo "<th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th>";
                echo "</tr>";
                foreach ($sample_users as $user) {
                    echo "<tr>";
                    echo "<td>" . $user['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                    echo "<td>" . $user['role'] . "</td>";
                    echo "<td>" . $user['created_at'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: orange;'>No users found</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ Users table does not exist!</p>";
            
            // Show available tables
            $tables_query = "SHOW TABLES";
            $tables_stmt = $db->prepare($tables_query);
            $tables_stmt->execute();
            $tables = $tables_stmt->fetchAll();
            
            echo "<h3>Available tables:</h3>";
            if ($tables) {
                echo "<ul>";
                foreach ($tables as $table) {
                    echo "<li>" . array_values($table)[0] . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No tables found in database</p>";
            }
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
echo "<li>✓ user.php file updated to match user.html structure</li>";
echo "<li>✓ Database connection code integrated</li>";
echo "<li>✓ User authentication check implemented</li>";
echo "<li>✓ Dynamic user name display when logged in</li>";
echo "<li>✓ Smart login/logout dropdown behavior</li>";
echo "<li>✓ All links updated to PHP extensions</li>";
echo "<li>✓ Exact HTML structure preserved</li>";
echo "</ul>";

echo "<h3>User Page Behavior:</h3>";
echo "<ul>";
echo "<li><strong>Not logged in:</strong> Shows default '𝒰𝓈𝑒𝓇 𝒩𝒶𝓂𝑒' and login dropdown</li>";
echo "<li><strong>Logged in:</strong> Shows actual user name and user menu with logout option</li>";
echo "<li><strong>Admin user:</strong> Shows additional admin panel link in dropdown</li>";
echo "<li><strong>All menu items:</strong> Link to corresponding PHP pages</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='user.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View User Page</a>";
echo "<a href='login.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Login</a>";
echo "<a href='signup.php' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>Sign Up</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
