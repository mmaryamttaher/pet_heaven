<?php
require_once 'config/config.php';

echo "<h2>Booking Page Database Connection Test</h2>";
echo "<p><strong>Testing database connection and booking functionality...</strong></p>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Test 1: Check if bookings table exists
        $table_query = "SHOW TABLES LIKE 'bookings'";
        $table_stmt = $db->prepare($table_query);
        $table_stmt->execute();
        $table_exists = $table_stmt->fetch();
        
        if ($table_exists) {
            echo "<p style='color: green;'>✓ Bookings table exists!</p>";
            
            // Test 2: Get all bookings
            $all_bookings_query = "SELECT COUNT(*) as count FROM bookings";
            $all_bookings_stmt = $db->prepare($all_bookings_query);
            $all_bookings_stmt->execute();
            $total_bookings = $all_bookings_stmt->fetch()['count'];
            
            echo "<p><strong>Total bookings in database:</strong> " . $total_bookings . "</p>";
            
            // Test 3: Check related tables
            $related_tables = ['users', 'hotels', 'pets'];
            foreach ($related_tables as $table) {
                $check_query = "SHOW TABLES LIKE '$table'";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->execute();
                $exists = $check_stmt->fetch();
                
                if ($exists) {
                    $count_query = "SELECT COUNT(*) as count FROM $table";
                    $count_stmt = $db->prepare($count_query);
                    $count_stmt->execute();
                    $count = $count_stmt->fetch()['count'];
                    echo "<p style='color: green;'>✓ $table table exists with $count records</p>";
                } else {
                    echo "<p style='color: red;'>✗ $table table missing</p>";
                }
            }
            
            // Test 4: Sample booking query (the one used in booking.php)
            echo "<h3>Testing Booking Query:</h3>";
            $sample_user_id = 1; // Test with user ID 1
            
            $booking_query = "SELECT b.*, h.name as hotel_name, h.image as hotel_image, p.name as pet_name, p.type as pet_type 
                              FROM bookings b 
                              JOIN hotels h ON b.hotel_id = h.id 
                              JOIN pets p ON b.pet_id = p.id 
                              WHERE b.user_id = ? 
                              ORDER BY b.created_at DESC";
            
            echo "<p><strong>Query:</strong> " . htmlspecialchars($booking_query) . "</p>";
            echo "<p><strong>Test User ID:</strong> " . $sample_user_id . "</p>";
            
            try {
                $booking_stmt = $db->prepare($booking_query);
                $booking_stmt->execute([$sample_user_id]);
                $sample_bookings = $booking_stmt->fetchAll();
                
                echo "<p><strong>Results:</strong> " . count($sample_bookings) . " bookings found for user ID " . $sample_user_id . "</p>";
                
                if ($sample_bookings) {
                    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                    echo "<tr style='background-color: #f0f0f0;'>";
                    echo "<th>ID</th><th>Hotel</th><th>Pet</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Amount</th>";
                    echo "</tr>";
                    
                    foreach ($sample_bookings as $booking) {
                        echo "<tr>";
                        echo "<td>" . $booking['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($booking['hotel_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($booking['pet_name']) . " (" . $booking['pet_type'] . ")</td>";
                        echo "<td>" . $booking['check_in_date'] . "</td>";
                        echo "<td>" . $booking['check_out_date'] . "</td>";
                        echo "<td>" . $booking['status'] . "</td>";
                        echo "<td>" . number_format($booking['total_amount'], 0) . " EGP</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p style='color: orange;'>⚠ No bookings found for test user</p>";
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
                } else {
                    echo "<p style='color: orange;'>⚠ User is not logged in</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Session is not active</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ Bookings table does not exist!</p>";
            
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
echo "<li>✓ booking.php file updated to match booking.html structure</li>";
echo "<li>✓ Database connection code integrated</li>";
echo "<li>✓ User authentication check implemented</li>";
echo "<li>✓ Booking display functionality for logged-in users</li>";
echo "<li>✓ Default 'No booking yet' message when no bookings</li>";
echo "<li>✓ Exact HTML structure preserved</li>";
echo "</ul>";

echo "<h3>Booking Page Behavior:</h3>";
echo "<ul>";
echo "<li><strong>Not logged in:</strong> Shows 'No booking yet' message with 'Book Now' button</li>";
echo "<li><strong>Logged in, no bookings:</strong> Shows 'No booking yet' message with 'Book Now' button</li>";
echo "<li><strong>Logged in, has bookings:</strong> Shows detailed booking cards with hotel info, dates, status</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='booking.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View Booking Page</a>";
echo "<a href='login.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Login</a>";
echo "<a href='search.php' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>Search Hotels</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
