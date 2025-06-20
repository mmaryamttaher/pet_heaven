<?php
require_once 'config/config.php';

echo "<h2>Adding Giza Hotels to Database</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Check if Giza hotels already exist
        $check_query = "SELECT COUNT(*) as count FROM hotels WHERE city LIKE '%Giza%'";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute();
        $existing_count = $check_stmt->fetch()['count'];
        
        echo "<p>Existing Giza hotels: " . $existing_count . "</p>";
        
        // Add Giza hotels if they don't exist
        if ($existing_count == 0) {
            $hotels = [
                [
                    'name' => 'Eastwind',
                    'description' => 'Premium pet boarding facility with modern amenities and spacious rooms',
                    'address' => '26th of July Corridor, Kerdasa, Giza Governorate 12577',
                    'city' => 'Giza',
                    'price_per_day' => 1400.00,
                    'image' => 'imgs/2024-08-26.webp',
                    'amenities' => '["Modern Facilities", "Spacious Rooms", "24/7 Care", "Exercise Area"]',
                    'capacity' => 18
                ],
                [
                    'name' => 'Golden Paws',
                    'description' => 'Cozy pet boarding with personalized care and attention',
                    'address' => 'Elward st, Giza, Giza Government',
                    'city' => 'Giza',
                    'price_per_day' => 900.00,
                    'image' => 'imgs/cat13.jpg',
                    'amenities' => '["Personalized Care", "Cozy Environment", "Daily Updates", "Special Diet Care"]',
                    'capacity' => 12
                ]
            ];
            
            $insert_query = "INSERT INTO hotels (name, description, address, city, price_per_day, image, amenities, capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            
            foreach ($hotels as $hotel) {
                $insert_stmt->execute([
                    $hotel['name'],
                    $hotel['description'],
                    $hotel['address'],
                    $hotel['city'],
                    $hotel['price_per_day'],
                    $hotel['image'],
                    $hotel['amenities'],
                    $hotel['capacity']
                ]);
                echo "<p style='color: green;'>✓ Added hotel: " . $hotel['name'] . "</p>";
            }
            
            echo "<p style='color: green;'><strong>Successfully added " . count($hotels) . " Giza hotels!</strong></p>";
        } else {
            echo "<p style='color: orange;'>⚠ Giza hotels already exist in database</p>";
        }
        
        // Show current Giza hotels
        $giza_query = "SELECT id, name, city, price_per_day, status FROM hotels WHERE city LIKE '%Giza%'";
        $giza_stmt = $db->prepare($giza_query);
        $giza_stmt->execute();
        $giza_hotels = $giza_stmt->fetchAll();
        
        echo "<h3>Current Giza Hotels:</h3>";
        if ($giza_hotels) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Name</th><th>City</th><th>Price per Day</th><th>Status</th></tr>";
            foreach ($giza_hotels as $hotel) {
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
            echo "<p style='color: red;'>No Giza hotels found</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='giza.php' style='margin-right: 10px;'>← View Giza Page</a>";
echo "<a href='test_db.php' style='margin-right: 10px;'>Test Database</a>";
echo "<a href='index.php'>← Back to Home</a>";
?>
