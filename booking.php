<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setMessage('Please log in to view your bookings.', 'error');
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user's bookings
$user_bookings = [];
$total_bookings = 0;

try {
    // Try multiple table combinations to be flexible
    $bookings_query = "SELECT b.*,
                       COALESCE(h.name, ht.name) as host_name,
                       COALESCE(h.location, ht.location) as location,
                       COALESCE(h.price_per_day, ht.price_per_day) as price_per_day,
                       COALESCE(h.image, ht.image) as host_image,
                       p.name as pet_name, p.type as pet_type
                       FROM bookings b
                       LEFT JOIN hosts h ON b.host_id = h.id
                       LEFT JOIN hotels ht ON b.hotel_id = ht.id
                       LEFT JOIN pets p ON b.pet_id = p.id
                       WHERE b.user_id = ?
                       ORDER BY b.created_at DESC";
    $bookings_stmt = $db->prepare($bookings_query);
    $bookings_stmt->execute([$_SESSION['user_id']]);
    $user_bookings = $bookings_stmt->fetchAll();
    $total_bookings = count($user_bookings);
} catch (Exception $e) {
    // Tables might not exist, will show empty state
}

// Handle booking cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $booking_id = (int)$_GET['cancel'];

    try {
        // Check if booking belongs to user and can be cancelled
        $check_query = "SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status IN ('pending', 'confirmed')";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $check_stmt->fetch();

        if ($booking) {
            // Update booking status to cancelled
            $cancel_query = "UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
            $cancel_stmt = $db->prepare($cancel_query);

            if ($cancel_stmt->execute([$booking_id])) {
                setMessage('Booking cancelled successfully.', 'success');
            } else {
                setMessage('Error cancelling booking.', 'error');
            }
        } else {
            setMessage('Booking not found or cannot be cancelled.', 'error');
        }
    } catch (Exception $e) {
        setMessage('Error cancelling booking: ' . $e->getMessage(), 'error');
    }

    redirect('booking.php');
}

// Get booking statistics
$booking_stats = [
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'cancelled' => 0
];

try {
    $stats_query = "SELECT status, COUNT(*) as count FROM bookings WHERE user_id = ? GROUP BY status";
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->execute([$_SESSION['user_id']]);
    $stats = $stats_stmt->fetchAll();

    foreach ($stats as $stat) {
        if (isset($booking_stats[$stat['status']])) {
            $booking_stats[$stat['status']] = $stat['count'];
        }
    }
} catch (Exception $e) {
    // Use default stats
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking</title>
    <link rel="stylesheet" href="booking.css">
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://getbootstrap.com/docs/5.3/assets/css/docs.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!--start header-->
    <header class="navbar navbar-expand-lg  " style="position: fixed; " >
        <div class="container">
            <img src="imgs/Preview-removebg-preview.png" alt="logo" height="100" width="150">
            <a href="#menue" class="navbar-toggler collapsed"  data-bs-toggle=collapse data-bs-target="#menue"  aria-expanded="false" >
                <span class="navbar-toggler-icon "></span>
            </a>
            <nav class="collapse  navbar-collapse justify-content-end " id="menue"  >
                <ul >
                    <li class="navbar-item">
                        <a href="index.php" class="nav-link active">HOME</a>
                    </li>
                    <li class="navbar-item">
                        <a href="search.php" class="nav-link ">Search</a>
                    </li>
                    <li class="navbar-item">
                        <a href="#" class="nav-link ">Booking</a>
                    </li>
                    <li class="navbar-item">
                        <a href="#contact" class="nav-link">Contact Us</a>
                    </li>
                    <li class="navbar-item">
                        <a href="user.php" class="nav-link"><i class="fa-regular fa-user"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
<!--end header-->

<?php
// Display messages
$message = getMessage();
if ($message):
?>
<div class="alert alert-<?php echo $message['type'] == 'error' ? 'danger' : $message['type']; ?> alert-dismissible fade show" role="alert" style="margin-top: 120px;">
    <?php echo $message['message']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!--start home-->
<section id="main">

</section>
<!--end home-->

<!--start booking-->
<section id="booking">
    <?php if ($total_bookings == 0): ?>
    <!-- No bookings state (matches original HTML exactly) -->
    <div id="bookinglist">
        <img src="imgs/IMG_8269.JPG" alt="hosting" width="83px" height="85px">
        <h2>No booking yet</h2>
        <h5>When you place your first book,it will appear here</h5>
    </div>
    <div id="bookingbtn">
        <a href="booknow.php"><button class="btn btn-light">Book Now</button></a>
    </div>
    <?php else: ?>
    <!-- User has bookings -->
    <div class="container" style="margin-top: 20px;">
        <div class="row">
            <div class="col-12">
                <h2>My Bookings (<?php echo $total_bookings; ?>)</h2>

                <!-- Booking Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><?php echo $booking_stats['pending']; ?></h5>
                                <p class="card-text">Pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-success"><?php echo $booking_stats['confirmed']; ?></h5>
                                <p class="card-text">Confirmed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-primary"><?php echo $booking_stats['completed']; ?></h5>
                                <p class="card-text">Completed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-danger"><?php echo $booking_stats['cancelled']; ?></h5>
                                <p class="card-text">Cancelled</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bookings List -->
                <div class="row">
                    <?php foreach ($user_bookings as $booking): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Booking #<?php echo $booking['id']; ?></h6>
                                <span class="badge bg-<?php
                                    echo $booking['status'] == 'confirmed' ? 'success' :
                                        ($booking['status'] == 'pending' ? 'warning' :
                                        ($booking['status'] == 'completed' ? 'primary' : 'danger'));
                                ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-home"></i> <?php echo htmlspecialchars($booking['host_name'] ?? 'Host'); ?>
                                </h6>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($booking['location'] ?? 'Location not specified'); ?>
                                </p>
                                <?php if ($booking['pet_name']): ?>
                                <p class="card-text">
                                    <i class="fas fa-paw"></i> <?php echo htmlspecialchars($booking['pet_name']); ?> (<?php echo htmlspecialchars($booking['pet_type']); ?>)
                                </p>
                                <?php endif; ?>
                                <p class="card-text">
                                    <i class="fas fa-calendar"></i>
                                    <?php
                                    $start_date = $booking['start_date'] ?? $booking['check_in_date'] ?? 'N/A';
                                    $end_date = $booking['end_date'] ?? $booking['check_out_date'] ?? 'N/A';
                                    if ($start_date != 'N/A') echo date('M j, Y', strtotime($start_date)); else echo 'N/A';
                                    echo ' - ';
                                    if ($end_date != 'N/A') echo date('M j, Y', strtotime($end_date)); else echo 'N/A';
                                    ?>
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-dollar-sign"></i> $<?php echo number_format($booking['total_price'] ?? $booking['total_amount'] ?? 0, 2); ?>
                                </p>
                                <?php if (!empty($booking['special_instructions'])): ?>
                                <p class="card-text">
                                    <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($booking['special_instructions']); ?>
                                </p>
                                <?php endif; ?>
                                <small class="text-muted">
                                    Booked on <?php echo date('M j, Y', strtotime($booking['created_at'])); ?>
                                </small>
                            </div>
                            <div class="card-footer">
                                <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                <a href="booking.php?cancel=<?php echo $booking['id']; ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to cancel this booking?')">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <?php endif; ?>
                                <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Book Now Button -->
                <div class="text-center mt-4">
                    <a href="booknow.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Book Another Stay
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>
<!--end booking-->

<!--start footer-->
<footer id="contact">
    <img src="imgs/Preview-removebg-preview.png" alt="logo" height="100" width="150">
    <div id="cicons">
    <h4>𝘊𝘖𝘕𝘛𝘈𝘊𝘛 𝘜𝘚</h4>
    <div class="icons">
        <a href=""><i class="fa-brands fa-facebook" style="color: #2958a8;"></i></a>
        <a href=""><i class="fa-brands fa-instagram" style="color: #e713a4;"></i></a>
        <a href=""><i class="fa-brands fa-whatsapp" style="color: #398f00;"></i></a>
        <a href="chat.php"><i class="fa-regular fa-comments"></i></a>
    </div>
</div>
</footer>
<!--end footer-->
</body>
</html>
