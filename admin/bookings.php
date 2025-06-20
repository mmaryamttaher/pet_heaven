<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_status') {
        $booking_id = intval($_POST['booking_id']);
        $new_status = sanitizeInput($_POST['status']);
        
        $query = "UPDATE bookings SET status = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$new_status, $booking_id])) {
            setMessage('Booking status updated successfully!', 'success');
        } else {
            setMessage('Error updating booking status.', 'error');
        }
        redirect('bookings.php');
    }
}

// Get all bookings with related data
$query = "SELECT b.*, u.name as user_name, u.email as user_email, h.name as hotel_name, p.name as pet_name, p.type as pet_type 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN hotels h ON b.hotel_id = h.id 
          JOIN pets p ON b.pet_id = p.id 
          ORDER BY b.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$bookings = $stmt->fetchAll();

$page_title = "Manage Bookings";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
        }
        .main-content {
            margin-left: 0;
        }
        @media (min-width: 768px) {
            .main-content {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Pet Heaven Admin</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="hotels.php">
                                <i class="fas fa-hotel"></i> Hotels
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="bookings.php">
                                <i class="fas fa-calendar-check"></i> Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="services.php">
                                <i class="fas fa-concierge-bell"></i> Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="messages.php">
                                <i class="fas fa-comments"></i> Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-home"></i> Back to Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Bookings</h1>
                </div>

                <?php
                $message = getMessage();
                if ($message):
                ?>
                <div class="alert alert-<?php echo $message['type'] == 'error' ? 'danger' : $message['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Bookings Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking #</th>
                                        <th>Customer</th>
                                        <th>Hotel</th>
                                        <th>Pet</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo $booking['booking_reference']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($booking['user_name']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($booking['user_email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['pet_name']); ?> (<?php echo ucfirst($booking['pet_type']); ?>)</td>
                                        <td><?php echo formatDate($booking['check_in_date']); ?></td>
                                        <td><?php echo formatDate($booking['check_out_date']); ?></td>
                                        <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $booking['status'] == 'confirmed' ? 'success' : 
                                                    ($booking['status'] == 'pending' ? 'warning' : 
                                                    ($booking['status'] == 'cancelled' ? 'danger' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Update Status
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php if ($booking['status'] != 'confirmed'): ?>
                                                    <li>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <input type="hidden" name="status" value="confirmed">
                                                            <button type="submit" class="dropdown-item">Confirm</button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if ($booking['status'] != 'cancelled'): ?>
                                                    <li>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <input type="hidden" name="status" value="cancelled">
                                                            <button type="submit" class="dropdown-item">Cancel</button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                                    <li>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <input type="hidden" name="status" value="checked_in">
                                                            <button type="submit" class="dropdown-item">Check In</button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if ($booking['status'] == 'checked_in'): ?>
                                                    <li>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <input type="hidden" name="status" value="checked_out">
                                                            <button type="submit" class="dropdown-item">Check Out</button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
