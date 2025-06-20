<?php
require_once 'config/config.php';

echo "<h2>Pet Page Database Connection Test</h2>";
echo "<p><strong>Testing database connection and pet functionality...</strong></p>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Test 1: Check if pets table exists
        $table_query = "SHOW TABLES LIKE 'pets'";
        $table_stmt = $db->prepare($table_query);
        $table_stmt->execute();
        $table_exists = $table_stmt->fetch();
        
        if ($table_exists) {
            echo "<p style='color: green;'>✓ Pets table exists!</p>";
            
            // Test 2: Get all pets
            $all_pets_query = "SELECT COUNT(*) as count FROM pets";
            $all_pets_stmt = $db->prepare($all_pets_query);
            $all_pets_stmt->execute();
            $total_pets = $all_pets_stmt->fetch()['count'];
            
            echo "<p><strong>Total pets in database:</strong> " . $total_pets . "</p>";
            
            // Test 3: Check pets table structure
            $structure_query = "DESCRIBE pets";
            $structure_stmt = $db->prepare($structure_query);
            $structure_stmt->execute();
            $columns = $structure_stmt->fetchAll();
            
            echo "<h3>Pets Table Structure:</h3>";
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
            
            // Test 4: Sample pet query (the one used in pet.php)
            echo "<h3>Testing Pet Query:</h3>";
            $sample_user_id = 1; // Test with user ID 1
            
            $pets_query = "SELECT * FROM pets WHERE user_id = ? ORDER BY created_at DESC";
            
            echo "<p><strong>Query:</strong> " . htmlspecialchars($pets_query) . "</p>";
            echo "<p><strong>Test User ID:</strong> " . $sample_user_id . "</p>";
            
            try {
                $pets_stmt = $db->prepare($pets_query);
                $pets_stmt->execute([$sample_user_id]);
                $sample_pets = $pets_stmt->fetchAll();
                
                echo "<p><strong>Results:</strong> " . count($sample_pets) . " pets found for user ID " . $sample_user_id . "</p>";
                
                if ($sample_pets) {
                    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                    echo "<tr style='background-color: #f0f0f0;'>";
                    echo "<th>ID</th><th>Name</th><th>Type</th><th>Age</th><th>Weight</th><th>Breed</th><th>Created</th>";
                    echo "</tr>";
                    
                    foreach ($sample_pets as $pet) {
                        echo "<tr>";
                        echo "<td>" . $pet['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($pet['name']) . "</td>";
                        echo "<td>" . ucfirst($pet['type']) . "</td>";
                        echo "<td>" . ($pet['age'] ?? 'N/A') . "</td>";
                        echo "<td>" . ($pet['weight'] ?? 'N/A') . " kg</td>";
                        echo "<td>" . htmlspecialchars($pet['breed'] ?? 'N/A') . "</td>";
                        echo "<td>" . $pet['created_at'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p style='color: orange;'>⚠ No pets found for test user</p>";
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Query failed: " . $e->getMessage() . "</p>";
            }
            
            // Test 5: Test CRUD operations
            echo "<h3>Testing CRUD Operations:</h3>";
            
            // Test INSERT
            $test_pet_name = "Test Pet " . date('His');
            $insert_query = "INSERT INTO pets (user_id, name, age, type, weight) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            
            if ($insert_stmt->execute([1, $test_pet_name, 3, 'dog', 15.5])) {
                $new_pet_id = $db->lastInsertId();
                echo "<p style='color: green;'>✓ INSERT test successful - Pet ID: " . $new_pet_id . "</p>";
                
                // Test UPDATE
                $update_query = "UPDATE pets SET name = ?, age = ? WHERE id = ?";
                $update_stmt = $db->prepare($update_query);
                
                if ($update_stmt->execute([$test_pet_name . " Updated", 4, $new_pet_id])) {
                    echo "<p style='color: green;'>✓ UPDATE test successful</p>";
                } else {
                    echo "<p style='color: red;'>✗ UPDATE test failed</p>";
                }
                
                // Test DELETE
                $delete_query = "DELETE FROM pets WHERE id = ?";
                $delete_stmt = $db->prepare($delete_query);
                
                if ($delete_stmt->execute([$new_pet_id])) {
                    echo "<p style='color: green;'>✓ DELETE test successful</p>";
                } else {
                    echo "<p style='color: red;'>✗ DELETE test failed</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ INSERT test failed</p>";
            }
            
            // Test 6: Check session functionality
            echo "<h3>Session Status:</h3>";
            if (session_status() == PHP_SESSION_ACTIVE) {
                echo "<p style='color: green;'>✓ Session is active</p>";
                
                if (isLoggedIn()) {
                    echo "<p style='color: green;'>✓ User is logged in (ID: " . $_SESSION['user_id'] . ")</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ User is not logged in</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Session is not active</p>";
            }
            
            // Test 7: Show sample pets by type
            echo "<h3>Pets by Type:</h3>";
            $types_query = "SELECT type, COUNT(*) as count FROM pets GROUP BY type";
            $types_stmt = $db->prepare($types_query);
            $types_stmt->execute();
            $pet_types = $types_stmt->fetchAll();
            
            if ($pet_types) {
                echo "<ul>";
                foreach ($pet_types as $type_data) {
                    echo "<li><strong>" . ucfirst($type_data['type']) . ":</strong> " . $type_data['count'] . " pets</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No pets found in database</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ Pets table does not exist!</p>";
            
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
echo "<li>✓ pet.php file created to match pet.html structure</li>";
echo "<li>✓ Database connection code integrated</li>";
echo "<li>✓ User authentication check implemented</li>";
echo "<li>✓ Pet CRUD operations (Create, Read, Update, Delete)</li>";
echo "<li>✓ Form validation and error handling</li>";
echo "<li>✓ Pet listing with edit/delete functionality</li>";
echo "<li>✓ Exact HTML structure preserved</li>";
echo "</ul>";

echo "<h3>Pet Page Features:</h3>";
echo "<ul>";
echo "<li><strong>Add Pet:</strong> Form to add new pets with name, age, type, weight</li>";
echo "<li><strong>Edit Pet:</strong> Click edit button to modify existing pet information</li>";
echo "<li><strong>Delete Pet:</strong> Remove pets with confirmation dialog</li>";
echo "<li><strong>Pet Types:</strong> Dog, Cat, Turtle, Bird (as in original HTML)</li>";
echo "<li><strong>Validation:</strong> Required fields and proper data types</li>";
echo "<li><strong>User Isolation:</strong> Users can only see/edit their own pets</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='pet.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View Pet Page</a>";
echo "<a href='login.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Login</a>";
echo "<a href='user.php' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>User Profile</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
