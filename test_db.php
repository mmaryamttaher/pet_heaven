<?php
require_once 'config/config.php';

echo "<h2>Database Connection Test</h2>";
echo "<p><strong>Database Configuration:</strong></p>";
echo "<ul>";
echo "<li>Host: " . DB_HOST . "</li>";
echo "<li>Database: " . DB_NAME . "</li>";
echo "<li>User: " . DB_USER . "</li>";
echo "<li>Password: " . (empty(DB_PASS) ? 'Empty' : 'Set') . "</li>";
echo "</ul>";

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";

        // Test if hotels table exists
        $query = "SHOW TABLES LIKE 'hotels'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            echo "<p style='color: green;'>✓ Hotels table exists!</p>";

            // Count hotels
            $count_query = "SELECT COUNT(*) as count FROM hotels";
            $count_stmt = $db->prepare($count_query);
            $count_stmt->execute();
            $count_result = $count_stmt->fetch();

            echo "<p>Total hotels in database: " . $count_result['count'] . "</p>";

            // Show Cairo hotels specifically
            $cairo_query = "SELECT id, name, city, price_per_day, status FROM hotels WHERE city LIKE '%Cairo%'";
            $cairo_stmt = $db->prepare($cairo_query);
            $cairo_stmt->execute();
            $cairo_hotels = $cairo_stmt->fetchAll();

            echo "<h3>Cairo Hotels:</h3>";
            if ($cairo_hotels) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Name</th><th>City</th><th>Price per Day</th><th>Status</th></tr>";
                foreach ($cairo_hotels as $hotel) {
                    echo "<tr>";
                    echo "<td>" . $hotel['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($hotel['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($hotel['city']) . "</td>";
                    echo "<td>" . $hotel['price_per_day'] . "</td>";
                    echo "<td>" . $hotel['status'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: orange;'>⚠ No Cairo hotels found in database</p>";
            }

            // Show all hotels
            $all_query = "SELECT id, name, city, price_per_day, status FROM hotels LIMIT 10";
            $all_stmt = $db->prepare($all_query);
            $all_stmt->execute();
            $all_hotels = $all_stmt->fetchAll();

            echo "<h3>All Hotels (First 10):</h3>";
            if ($all_hotels) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Name</th><th>City</th><th>Price per Day</th><th>Status</th></tr>";
                foreach ($all_hotels as $hotel) {
                    echo "<tr>";
                    echo "<td>" . $hotel['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($hotel['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($hotel['city']) . "</td>";
                    echo "<td>" . $hotel['price_per_day'] . "</td>";
                    echo "<td>" . $hotel['status'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: orange;'>⚠ No hotels found in database</p>";
            }

        } else {
            echo "<p style='color: red;'>✗ Hotels table does not exist!</p>";

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

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='database/init.php' style='margin-right: 10px;'>Initialize Database</a>";
echo "<a href='cairo.php' style='margin-right: 10px;'>← Back to Cairo page</a>";
echo "<a href='index.php'>← Back to Home</a>";
?>
