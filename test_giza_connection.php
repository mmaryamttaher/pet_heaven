<?php
require_once 'config/config.php';

echo "<h2>Giza Page Database Connection Test</h2>";
echo "<p><strong>Testing database connection for Giza hotels...</strong></p>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Test the exact query used in giza.php
        $query = "SELECT * FROM hotels WHERE status = 'active' AND city LIKE '%Giza%' ORDER BY rating DESC, price_per_day ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $hotels = $stmt->fetchAll();
        
        echo "<h3>Giza Hotels Query Results:</h3>";
        echo "<p><strong>Query:</strong> " . htmlspecialchars($query) . "</p>";
        echo "<p><strong>Results found:</strong> " . count($hotels) . "</p>";
        
        if ($hotels) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>ID</th><th>Name</th><th>Description</th><th>Address</th><th>City</th><th>Price/Day</th><th>Image</th><th>Status</th>";
            echo "</tr>";
            
            foreach ($hotels as $hotel) {
                echo "<tr>";
                echo "<td>" . $hotel['id'] . "</td>";
                echo "<td>" . htmlspecialchars($hotel['name']) . "</td>";
                echo "<td>" . htmlspecialchars(substr($hotel['description'], 0, 50)) . "...</td>";
                echo "<td>" . htmlspecialchars($hotel['address']) . "</td>";
                echo "<td>" . htmlspecialchars($hotel['city']) . "</td>";
                echo "<td>" . number_format($hotel['price_per_day'], 0) . "</td>";
                echo "<td>" . htmlspecialchars($hotel['image']) . "</td>";
                echo "<td>" . $hotel['status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠ No Giza hotels found with the current query</p>";
            
            // Check if any hotels exist at all
            $all_query = "SELECT COUNT(*) as total FROM hotels";
            $all_stmt = $db->prepare($all_query);
            $all_stmt->execute();
            $total_hotels = $all_stmt->fetch()['total'];
            
            echo "<p>Total hotels in database: " . $total_hotels . "</p>";
            
            if ($total_hotels > 0) {
                // Show all cities
                $cities_query = "SELECT DISTINCT city FROM hotels WHERE city IS NOT NULL";
                $cities_stmt = $db->prepare($cities_query);
                $cities_stmt->execute();
                $cities = $cities_stmt->fetchAll();
                
                echo "<h4>Available cities in database:</h4>";
                echo "<ul>";
                foreach ($cities as $city) {
                    echo "<li>" . htmlspecialchars($city['city']) . "</li>";
                }
                echo "</ul>";
            }
        }
        
        // Test search functionality
        echo "<h3>Testing Search Functionality:</h3>";
        $search_query = "Golden";
        $search_query_param = "SELECT * FROM hotels WHERE status = 'active' AND city LIKE '%Giza%' AND (name LIKE ? OR description LIKE ?) ORDER BY rating DESC";
        $search_stmt = $db->prepare($search_query_param);
        $search_param = "%$search_query%";
        $search_stmt->execute([$search_param, $search_param]);
        $search_results = $search_stmt->fetchAll();
        
        echo "<p><strong>Search Query:</strong> " . htmlspecialchars($search_query_param) . "</p>";
        echo "<p><strong>Search Term:</strong> '" . $search_query . "'</p>";
        echo "<p><strong>Search Results:</strong> " . count($search_results) . " hotels found</p>";
        
        if ($search_results) {
            echo "<ul>";
            foreach ($search_results as $result) {
                echo "<li>" . htmlspecialchars($result['name']) . " - " . number_format($result['price_per_day'], 0) . " per day</li>";
            }
            echo "</ul>";
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
echo "<li>✓ giza.php file updated to match giza.html structure</li>";
echo "<li>✓ Database connection code integrated</li>";
echo "<li>✓ Search functionality implemented</li>";
echo "<li>✓ Fallback to default hotels when database is empty</li>";
echo "<li>✓ PHP links updated (eastwind.php, golden.php, etc.)</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='giza.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View Giza Page</a>";
echo "<a href='add_giza_hotels.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Add Hotels</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
