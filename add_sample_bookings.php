<?php
require_once 'config/config.php';

echo "<h2>Adding Sample Bookings to Database</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // First, check if we have the required data
        $users_query = "SELECT COUNT(*) as count FROM users";
        $users_stmt = $db->prepare($users_query);
        $users_stmt->execute();
        $users_count = $users_stmt->fetch()['count'];
        
        $hotels_query = "SELECT COUNT(*) as count FROM hotels";
        $hotels_stmt = $db->prepare($hotels_query);
        $hotels_stmt->execute();
        $hotels_count = $hotels_stmt->fetch()['count'];
        
        echo "<p>Users in database: " . $users_count . "</p>";
        echo "<p>Hotels in database: " . $hotels_count . "</p>";
        
        if ($users_count == 0) {
            // Add a sample user
            $user_query = "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)";
            $user_stmt = $db->prepare($user_query);
            $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
            $user_stmt->execute(['John Doe', 'john@example.com', $hashed_password, '01234567890', 'user']);
            echo "<p style='color: green;'>✓ Added sample user: john@example.com</p>";
        }
        
        if ($hotels_count == 0) {
            echo "<p style='color: orange;'>⚠ No hotels found. Please run database initialization first.</p>";
            echo "<a href='database/init.php'>Initialize Database</a>";
            return;
        }
        
        // Get first user and hotel for sample booking
        $user_query = "SELECT id FROM users LIMIT 1";
        $user_stmt = $db->prepare($user_query);
        $user_stmt->execute();
        $user = $user_stmt->fetch();
        
        $hotel_query = "SELECT id FROM hotels LIMIT 1";
        $hotel_stmt = $db->prepare($hotel_query);
        $hotel_stmt->execute();
        $hotel = $hotel_stmt->fetch();
        
        if (!$user || !$hotel) {
            echo "<p style='color: red;'>✗ Missing user or hotel data</p>";
            return;
        }
        
        // Check if pets table exists and add sample pet
        $pet_check = "SHOW TABLES LIKE 'pets'";
        $pet_check_stmt = $db->prepare($pet_check);
        $pet_check_stmt->execute();
        $pets_table_exists = $pet_check_stmt->fetch();
        
        if ($pets_table_exists) {
            // Check if user has pets
            $pet_query = "SELECT id FROM pets WHERE user_id = ? LIMIT 1";
            $pet_stmt = $db->prepare($pet_query);
            $pet_stmt->execute([$user['id']]);
            $pet = $pet_stmt->fetch();
            
            if (!$pet) {
                // Add sample pet
                $add_pet_query = "INSERT INTO pets (user_id, name, type, breed, age, weight) VALUES (?, ?, ?, ?, ?, ?)";
                $add_pet_stmt = $db->prepare($add_pet_query);
                $add_pet_stmt->execute([$user['id'], 'Buddy', 'dog', 'Golden Retriever', 3, 25.5]);
                
                // Get the pet ID
                $pet_query = "SELECT id FROM pets WHERE user_id = ? LIMIT 1";
                $pet_stmt = $db->prepare($pet_query);
                $pet_stmt->execute([$user['id']]);
                $pet = $pet_stmt->fetch();
                
                echo "<p style='color: green;'>✓ Added sample pet: Buddy</p>";
            }
            
            // Check if bookings already exist
            $booking_check = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?";
            $booking_check_stmt = $db->prepare($booking_check);
            $booking_check_stmt->execute([$user['id']]);
            $existing_bookings = $booking_check_stmt->fetch()['count'];
            
            if ($existing_bookings == 0) {
                // Add sample bookings
                $bookings = [
                    [
                        'user_id' => $user['id'],
                        'hotel_id' => $hotel['id'],
                        'pet_id' => $pet['id'],
                        'check_in_date' => date('Y-m-d', strtotime('+7 days')),
                        'check_out_date' => date('Y-m-d', strtotime('+10 days')),
                        'total_days' => 3,
                        'total_amount' => 3600.00,
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                        'booking_reference' => 'BK' . date('Ymd') . '001'
                    ],
                    [
                        'user_id' => $user['id'],
                        'hotel_id' => $hotel['id'],
                        'pet_id' => $pet['id'],
                        'check_in_date' => date('Y-m-d', strtotime('+14 days')),
                        'check_out_date' => date('Y-m-d', strtotime('+17 days')),
                        'total_days' => 3,
                        'total_amount' => 3600.00,
                        'status' => 'pending',
                        'payment_status' => 'pending',
                        'booking_reference' => 'BK' . date('Ymd') . '002'
                    ]
                ];
                
                $booking_insert = "INSERT INTO bookings (user_id, hotel_id, pet_id, check_in_date, check_out_date, total_days, total_amount, status, payment_status, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $booking_stmt = $db->prepare($booking_insert);
                
                foreach ($bookings as $booking) {
                    $booking_stmt->execute([
                        $booking['user_id'],
                        $booking['hotel_id'],
                        $booking['pet_id'],
                        $booking['check_in_date'],
                        $booking['check_out_date'],
                        $booking['total_days'],
                        $booking['total_amount'],
                        $booking['status'],
                        $booking['payment_status'],
                        $booking['booking_reference']
                    ]);
                    echo "<p style='color: green;'>✓ Added booking: " . $booking['booking_reference'] . "</p>";
                }
                
                echo "<p style='color: green;'><strong>Successfully added " . count($bookings) . " sample bookings!</strong></p>";
            } else {
                echo "<p style='color: orange;'>⚠ Sample bookings already exist (" . $existing_bookings . " bookings)</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ Pets table does not exist</p>";
        }
        
        // Show current bookings
        $current_bookings_query = "SELECT b.*, h.name as hotel_name, p.name as pet_name FROM bookings b JOIN hotels h ON b.hotel_id = h.id JOIN pets p ON b.pet_id = p.id ORDER BY b.created_at DESC LIMIT 5";
        $current_bookings_stmt = $db->prepare($current_bookings_query);
        $current_bookings_stmt->execute();
        $current_bookings = $current_bookings_stmt->fetchAll();
        
        echo "<h3>Current Bookings (Latest 5):</h3>";
        if ($current_bookings) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Reference</th><th>Hotel</th><th>Pet</th><th>Check-in</th><th>Status</th><th>Amount</th></tr>";
            foreach ($current_bookings as $booking) {
                echo "<tr>";
                echo "<td>" . $booking['booking_reference'] . "</td>";
                echo "<td>" . htmlspecialchars($booking['hotel_name']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['pet_name']) . "</td>";
                echo "<td>" . $booking['check_in_date'] . "</td>";
                echo "<td>" . $booking['status'] . "</td>";
                echo "<td>" . number_format($booking['total_amount'], 0) . " EGP</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No bookings found</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='booking.php' style='margin-right: 10px;'>← View Booking Page</a>";
echo "<a href='login.php' style='margin-right: 10px;'>Login to Test</a>";
echo "<a href='test_booking_connection.php' style='margin-right: 10px;'>Test Connection</a>";
echo "<a href='index.php'>← Back to Home</a>";
?>
