<?php
require_once 'config/config.php';

echo "<h2>Setting Up Bookings Database</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Check if bookings table exists
        $table_query = "SHOW TABLES LIKE 'bookings'";
        $table_stmt = $db->prepare($table_query);
        $table_stmt->execute();
        $table_exists = $table_stmt->fetch();
        
        if (!$table_exists) {
            // Create bookings table
            $create_table_sql = "
                CREATE TABLE bookings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    host_id INT,
                    hotel_id INT,
                    pet_id INT,
                    start_date DATE,
                    end_date DATE,
                    check_in_date DATE,
                    check_out_date DATE,
                    total_days INT,
                    total_price DECIMAL(10,2),
                    total_amount DECIMAL(10,2),
                    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
                    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
                    special_instructions TEXT,
                    booking_reference VARCHAR(50),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_user_id (user_id),
                    INDEX idx_status (status),
                    INDEX idx_dates (start_date, end_date),
                    INDEX idx_booking_ref (booking_reference)
                )
            ";
            
            if ($db->exec($create_table_sql)) {
                echo "<p style='color: green;'>✓ Bookings table created successfully!</p>";
            } else {
                echo "<p style='color: red;'>✗ Error creating bookings table</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ Bookings table already exists!</p>";
        }
        
        // Check if hosts table exists
        $hosts_query = "SHOW TABLES LIKE 'hosts'";
        $hosts_stmt = $db->prepare($hosts_query);
        $hosts_stmt->execute();
        $hosts_exists = $hosts_stmt->fetch();
        
        if (!$hosts_exists) {
            // Create hosts table
            $create_hosts_sql = "
                CREATE TABLE hosts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    location VARCHAR(255),
                    price_per_day DECIMAL(10,2),
                    image VARCHAR(255),
                    description TEXT,
                    rating DECIMAL(3,2) DEFAULT 0.00,
                    total_reviews INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ";
            
            if ($db->exec($create_hosts_sql)) {
                echo "<p style='color: green;'>✓ Hosts table created successfully!</p>";
                
                // Insert sample hosts
                $sample_hosts = [
                    ['Pet Paradise Hotel', 'Downtown Cairo', 50.00, 'imgs/host1.jpg', 'Luxury pet hotel with 24/7 care'],
                    ['Happy Paws Resort', 'New Cairo', 35.00, 'imgs/host2.jpg', 'Family-friendly pet boarding'],
                    ['Furry Friends Lodge', 'Maadi', 40.00, 'imgs/host3.jpg', 'Cozy pet accommodation'],
                    ['Pet Palace', 'Zamalek', 60.00, 'imgs/host4.jpg', 'Premium pet care services'],
                    ['Animal Haven', 'Heliopolis', 30.00, 'imgs/host5.jpg', 'Affordable pet boarding']
                ];
                
                $insert_host_query = "INSERT INTO hosts (name, location, price_per_day, image, description) VALUES (?, ?, ?, ?, ?)";
                $insert_host_stmt = $db->prepare($insert_host_query);
                
                foreach ($sample_hosts as $host) {
                    $insert_host_stmt->execute($host);
                }
                
                echo "<p style='color: green;'>✓ Sample hosts added!</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ Hosts table already exists!</p>";
        }
        
        // Check if pets table exists
        $pets_query = "SHOW TABLES LIKE 'pets'";
        $pets_stmt = $db->prepare($pets_query);
        $pets_stmt->execute();
        $pets_exists = $pets_stmt->fetch();
        
        if (!$pets_exists) {
            // Create pets table
            $create_pets_sql = "
                CREATE TABLE pets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    type ENUM('dog', 'cat', 'bird', 'rabbit', 'other') NOT NULL,
                    breed VARCHAR(100),
                    age INT,
                    weight DECIMAL(5,2),
                    special_needs TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ";
            
            if ($db->exec($create_pets_sql)) {
                echo "<p style='color: green;'>✓ Pets table created successfully!</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ Pets table already exists!</p>";
        }
        
        // Show table structure
        $structure_query = "DESCRIBE bookings";
        $structure_stmt = $db->prepare($structure_query);
        $structure_stmt->execute();
        $columns = $structure_stmt->fetchAll();
        
        echo "<h3>Bookings Table Structure:</h3>";
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
        
        // Insert sample bookings if table is empty
        $count_query = "SELECT COUNT(*) as count FROM bookings";
        $count_stmt = $db->prepare($count_query);
        $count_stmt->execute();
        $total_bookings = $count_stmt->fetch()['count'];
        
        if ($total_bookings == 0) {
            echo "<p style='color: orange;'>⚠ No bookings found. Adding sample bookings...</p>";
            
            // Get some user IDs and host IDs for sample data
            $users_query = "SELECT id FROM users LIMIT 3";
            $users_stmt = $db->prepare($users_query);
            $users_stmt->execute();
            $users = $users_stmt->fetchAll();
            
            $hosts_query = "SELECT id FROM hosts LIMIT 3";
            $hosts_stmt = $db->prepare($hosts_query);
            $hosts_stmt->execute();
            $hosts = $hosts_stmt->fetchAll();
            
            if ($users && $hosts) {
                $sample_bookings = [
                    [
                        'user_id' => $users[0]['id'],
                        'host_id' => $hosts[0]['id'],
                        'start_date' => date('Y-m-d', strtotime('+7 days')),
                        'end_date' => date('Y-m-d', strtotime('+10 days')),
                        'total_days' => 3,
                        'total_price' => 150.00,
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                        'booking_reference' => 'BK' . date('Ymd') . '001'
                    ],
                    [
                        'user_id' => $users[1]['id'] ?? $users[0]['id'],
                        'host_id' => $hosts[1]['id'] ?? $hosts[0]['id'],
                        'start_date' => date('Y-m-d', strtotime('+14 days')),
                        'end_date' => date('Y-m-d', strtotime('+17 days')),
                        'total_days' => 3,
                        'total_price' => 105.00,
                        'status' => 'pending',
                        'payment_status' => 'pending',
                        'booking_reference' => 'BK' . date('Ymd') . '002'
                    ]
                ];
                
                $insert_query = "INSERT INTO bookings (user_id, host_id, start_date, end_date, total_days, total_price, status, payment_status, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $db->prepare($insert_query);
                
                foreach ($sample_bookings as $booking) {
                    $insert_stmt->execute([
                        $booking['user_id'],
                        $booking['host_id'],
                        $booking['start_date'],
                        $booking['end_date'],
                        $booking['total_days'],
                        $booking['total_price'],
                        $booking['status'],
                        $booking['payment_status'],
                        $booking['booking_reference']
                    ]);
                }
                
                echo "<p style='color: green;'>✓ Sample bookings added successfully!</p>";
            } else {
                echo "<p style='color: orange;'>⚠ No users or hosts found to create sample bookings</p>";
            }
        }
        
        // Show current bookings
        $bookings_query = "SELECT COUNT(*) as count FROM bookings";
        $bookings_stmt = $db->prepare($bookings_query);
        $bookings_stmt->execute();
        $total_bookings = $bookings_stmt->fetch()['count'];
        
        echo "<p><strong>Total bookings in database:</strong> " . $total_bookings . "</p>";
        
        if ($total_bookings > 0) {
            // Show booking statistics
            $stats_query = "SELECT 
                            status,
                            COUNT(*) as count,
                            SUM(total_price) as total_revenue
                            FROM bookings 
                            GROUP BY status";
            $stats_stmt = $db->prepare($stats_query);
            $stats_stmt->execute();
            $stats = $stats_stmt->fetchAll();
            
            echo "<h3>Booking Statistics:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>Status</th><th>Count</th><th>Total Revenue</th>";
            echo "</tr>";
            foreach ($stats as $stat) {
                echo "<tr>";
                echo "<td>" . ucfirst($stat['status']) . "</td>";
                echo "<td>" . $stat['count'] . "</td>";
                echo "<td>$" . number_format($stat['total_revenue'], 2) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Show recent bookings
            $recent_query = "SELECT b.*, u.name as user_name, h.name as host_name 
                            FROM bookings b 
                            LEFT JOIN users u ON b.user_id = u.id 
                            LEFT JOIN hosts h ON b.host_id = h.id 
                            ORDER BY b.created_at DESC 
                            LIMIT 5";
            $recent_stmt = $db->prepare($recent_query);
            $recent_stmt->execute();
            $recent_bookings = $recent_stmt->fetchAll();
            
            if ($recent_bookings) {
                echo "<h3>Recent Bookings:</h3>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
                echo "<tr style='background-color: #f0f0f0;'>";
                echo "<th>ID</th><th>User</th><th>Host</th><th>Dates</th><th>Status</th><th>Total</th>";
                echo "</tr>";
                foreach ($recent_bookings as $booking) {
                    echo "<tr>";
                    echo "<td>#" . $booking['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($booking['user_name'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($booking['host_name'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($booking['start_date'] ?? 'N/A') . " to " . ($booking['end_date'] ?? 'N/A') . "</td>";
                    echo "<td>" . ucfirst($booking['status']) . "</td>";
                    echo "<td>$" . number_format($booking['total_price'] ?? 0, 2) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ No bookings found. Bookings will be created when users make reservations.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Booking Features:</h3>";
echo "<ul>";
echo "<li><strong>Booking Management:</strong> View, cancel, and track booking status</li>";
echo "<li><strong>Multiple Providers:</strong> Support for both hosts and hotels</li>";
echo "<li><strong>Pet Integration:</strong> Link bookings to specific pets</li>";
echo "<li><strong>Status Tracking:</strong> Pending, Confirmed, Completed, Cancelled</li>";
echo "<li><strong>Payment Tracking:</strong> Payment status monitoring</li>";
echo "<li><strong>Booking Statistics:</strong> Dashboard with booking analytics</li>";
echo "<li><strong>Cancellation:</strong> Users can cancel pending/confirmed bookings</li>";
echo "</ul>";

echo "<h3>Technical Features:</h3>";
echo "<ul>";
echo "<li><strong>Database Integration:</strong> Bookings stored in bookings table</li>";
echo "<li><strong>Flexible Schema:</strong> Supports both host_id and hotel_id</li>";
echo "<li><strong>User Authentication:</strong> Login required to view bookings</li>";
echo "<li><strong>Responsive Design:</strong> Works on all devices</li>";
echo "<li><strong>Security:</strong> User isolation and input sanitization</li>";
echo "<li><strong>Analytics:</strong> Booking statistics and reporting</li>";
echo "<li><strong>Reference System:</strong> Unique booking reference numbers</li>";
echo "</ul>";

echo "<br><br>";
echo "<p><strong>Quick Actions:</strong></p>";
echo "<a href='booking.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>View Bookings Page</a>";
echo "<a href='booknow.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Book Now</a>";
echo "<a href='login.php' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>Login</a>";
echo "<a href='test_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 3px;'>Test Database</a>";
echo "<a href='index.php' style='padding: 5px 10px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;'>← Home</a>";
?>
