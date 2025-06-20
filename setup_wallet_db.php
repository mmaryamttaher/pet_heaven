<?php
require_once 'config/config.php';

echo "<h2>Setting Up Wallet Database</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Check if payment_cards table exists
        $table_query = "SHOW TABLES LIKE 'payment_cards'";
        $table_stmt = $db->prepare($table_query);
        $table_stmt->execute();
        $table_exists = $table_stmt->fetch();
        
        if (!$table_exists) {
            // Create payment_cards table
            $create_table_sql = "
                CREATE TABLE payment_cards (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    card_holder_name VARCHAR(100) NOT NULL,
                    masked_card_number VARCHAR(20) NOT NULL,
                    expiry_date VARCHAR(5) NOT NULL,
                    card_type VARCHAR(20) DEFAULT 'VISA',
                    is_default BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ";
            
            if ($db->exec($create_table_sql)) {
                echo "<p style='color: green;'>✓ Payment cards table created successfully!</p>";
            } else {
                echo "<p style='color: red;'>✗ Error creating payment cards table</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ Payment cards table already exists!</p>";
        }
        
        // Show table structure
        $structure_query = "DESCRIBE payment_cards";
        $structure_stmt = $db->prepare($structure_query);
        $structure_stmt->execute();
        $columns = $structure_stmt->fetchAll();
        
        echo "<h3>Payment Cards Table Structure:</h3>";
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
        
        // Check current cards
        $cards_query = "SELECT COUNT(*) as count FROM payment_cards";
        $cards_stmt = $db->prepare($cards_query);
        $cards_stmt->execute();
        $total_cards = $cards_stmt->fetch()['count'];
        
        echo "<p><strong>Total payment cards in database:</strong> " . $total_cards . "</p>";
        
        if ($total_cards == 0) {
            echo "<p style='color: orange;'>⚠ No payment cards found. You can add cards using the wallet page.</p>";
        } else {
            // Show sample cards (masked for security)
            $sample_cards_query = "SELECT pc.*, u.name as user_name FROM payment_cards pc JOIN users u ON pc.user_id = u.id LIMIT 5";
            $sample_cards_stmt = $db->prepare($sample_cards_query);
            $sample_cards_stmt->execute();
            $sample_cards = $sample_cards_stmt->fetchAll();
            
            echo "<h3>Sample Payment Cards:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>ID</th><th>User</th><th>Card Holder</th><th>Card Number</th><th>Type</th><th>Expiry</th><th>Created</th>";
            echo "</tr>";
            foreach ($sample_cards as $card) {
                echo "<tr>";
                echo "<td>" . $card['id'] . "</td>";
                echo "<td>" . htmlspecialchars($card['user_name']) . "</td>";
                echo "<td>" . htmlspecialchars($card['card_holder_name']) . "</td>";
                echo "<td>" . htmlspecialchars($card['masked_card_number']) . "</td>";
                echo "<td>" . $card['card_type'] . "</td>";
                echo "<td>" . $card['expiry_date'] . "</td>";
                echo "<td>" . $card['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Wallet Features:</h3>";
echo "<ul>";
echo "<li><strong>Add Payment Cards:</strong> Securely store payment card information</li>";
echo "<li><strong>Card Validation:</strong> Format validation for card numbers, expiry dates, CVV</li>";
echo "<li><strong>Card Type Detection:</strong> Automatically detects VISA, MASTERCARD, AMEX</li>";
echo "<li><strong>Masked Storage:</strong> Only stores masked card numbers for security</li>";
echo "<li><strong>User Isolation:</strong> Users can only see their own cards</li>";
echo "<li><strong>Delete Cards:</strong> Remove saved payment methods</li>";
echo "</ul>";

echo "<h3>Security Notes:</h3>";
echo "<ul>";
echo "<li><strong>Masked Numbers:</strong> Only last 4 digits are stored</li>";
echo "<li><strong>No CVV Storage:</strong> CVV is not stored in database</li>";
echo "<li><strong>User Authentication:</strong> Login required to access wallet</li>";
echo "<li><strong>Input Validation:</strong> Client and server-side validation</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='wallet.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View Wallet Page</a>";
echo "<a href='login.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Login</a>";
echo "<a href='user.php' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>User Profile</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
