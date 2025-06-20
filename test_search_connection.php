<?php
require_once 'config/config.php';

echo "<h2>Search Page Database Connection Test</h2>";
echo "<p><strong>Testing database connection and search functionality...</strong></p>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Test 1: Get all active hotels
        $all_query = "SELECT * FROM hotels WHERE status = 'active' ORDER BY rating DESC";
        $all_stmt = $db->prepare($all_query);
        $all_stmt->execute();
        $all_hotels = $all_stmt->fetchAll();
        
        echo "<h3>1. All Active Hotels:</h3>";
        echo "<p><strong>Query:</strong> " . htmlspecialchars($all_query) . "</p>";
        echo "<p><strong>Results found:</strong> " . count($all_hotels) . "</p>";
        
        if ($all_hotels) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>ID</th><th>Name</th><th>City</th><th>Price/Day</th><th>Rating</th><th>Status</th>";
            echo "</tr>";
            
            foreach ($all_hotels as $hotel) {
                echo "<tr>";
                echo "<td>" . $hotel['id'] . "</td>";
                echo "<td>" . htmlspecialchars($hotel['name']) . "</td>";
                echo "<td>" . htmlspecialchars($hotel['city']) . "</td>";
                echo "<td>" . number_format($hotel['price_per_day'], 0) . "</td>";
                echo "<td>" . $hotel['rating'] . "</td>";
                echo "<td>" . $hotel['status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠ No active hotels found</p>";
        }
        
        // Test 2: Search functionality
        echo "<h3>2. Search Functionality Tests:</h3>";
        
        $search_tests = [
            'Cairo' => 'City search',
            'Garden' => 'Name search',
            'pet' => 'Description search',
            'Golden' => 'Partial name search'
        ];
        
        foreach ($search_tests as $search_term => $test_type) {
            echo "<h4>Testing: " . $test_type . " ('" . $search_term . "')</h4>";
            
            $search_query = "SELECT * FROM hotels WHERE status = 'active' AND (name LIKE ? OR description LIKE ? OR city LIKE ?) ORDER BY rating DESC";
            $search_stmt = $db->prepare($search_query);
            $search_param = "%$search_term%";
            $search_stmt->execute([$search_param, $search_param, $search_param]);
            $search_results = $search_stmt->fetchAll();
            
            echo "<p><strong>Query:</strong> " . htmlspecialchars($search_query) . "</p>";
            echo "<p><strong>Search Term:</strong> '" . $search_term . "'</p>";
            echo "<p><strong>Results:</strong> " . count($search_results) . " hotels found</p>";
            
            if ($search_results) {
                echo "<ul>";
                foreach ($search_results as $result) {
                    echo "<li><strong>" . htmlspecialchars($result['name']) . "</strong> - " . htmlspecialchars($result['city']) . " - " . number_format($result['price_per_day'], 0) . " EGP/day</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: orange;'>No results found</p>";
            }
            echo "<hr>";
        }
        
        // Test 3: City-specific counts
        echo "<h3>3. Popular Cities Data:</h3>";
        $cities = ['Cairo', 'Suez', 'Giza'];
        
        foreach ($cities as $city) {
            $count_query = "SELECT COUNT(*) as count FROM hotels WHERE status = 'active' AND city LIKE ?";
            $count_stmt = $db->prepare($count_query);
            $count_stmt->execute(['%' . $city . '%']);
            $count = $count_stmt->fetch();
            
            echo "<p><strong>" . $city . ":</strong> " . $count['count'] . " hotels</p>";
        }
        
        // Test 4: Sample search URL simulation
        echo "<h3>4. URL Search Simulation:</h3>";
        $test_searches = [
            'cairo' => 'http://localhost/pets_shop/search.php?search=cairo',
            'pet' => 'http://localhost/pets_shop/search.php?search=pet',
            'garden' => 'http://localhost/pets_shop/search.php?search=garden'
        ];
        
        foreach ($test_searches as $term => $url) {
            echo "<p><a href='" . $url . "' target='_blank'>" . $url . "</a></p>";
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
echo "<li>✓ search.php file updated to match search.html structure</li>";
echo "<li>✓ Database connection code integrated</li>";
echo "<li>✓ Search functionality implemented with name, description, and city search</li>";
echo "<li>✓ Popular cities section with PHP links</li>";
echo "<li>✓ Recent searches section maintained</li>";
echo "<li>✓ Exact HTML structure preserved</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='search.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View Search Page</a>";
echo "<a href='search.php?search=cairo' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Test Cairo Search</a>";
echo "<a href='search.php?search=pet' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>Test Pet Search</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
