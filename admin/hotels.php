<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle hotel actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $address = sanitizeInput($_POST['address']);
                $city = sanitizeInput($_POST['city']);
                $price_per_day = floatval($_POST['price_per_day']);
                $capacity = intval($_POST['capacity']);
                $amenities = json_encode(explode(',', sanitizeInput($_POST['amenities'])));
                
                $query = "INSERT INTO hotels (name, description, address, city, price_per_day, capacity, amenities) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$name, $description, $address, $city, $price_per_day, $capacity, $amenities])) {
                    setMessage('Hotel added successfully!', 'success');
                } else {
                    setMessage('Error adding hotel.', 'error');
                }
                break;
                
            case 'toggle_status':
                $hotel_id = intval($_POST['hotel_id']);
                $current_status = $_POST['current_status'];
                $new_status = $current_status == 'active' ? 'inactive' : 'active';
                
                $query = "UPDATE hotels SET status = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$new_status, $hotel_id])) {
                    setMessage('Hotel status updated successfully!', 'success');
                } else {
                    setMessage('Error updating hotel status.', 'error');
                }
                break;
        }
        redirect('hotels.php');
    }
}

// Get all hotels
$query = "SELECT * FROM hotels ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$hotels = $stmt->fetchAll();

$page_title = "Manage Hotels";
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
                            <a class="nav-link active" href="hotels.php">
                                <i class="fas fa-hotel"></i> Hotels
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="bookings.php">
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
                    <h1 class="h2">Manage Hotels</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHotelModal">
                        <i class="fas fa-plus"></i> Add Hotel
                    </button>
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

                <!-- Hotels Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>City</th>
                                        <th>Price/Day</th>
                                        <th>Capacity</th>
                                        <th>Rating</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hotels as $hotel): ?>
                                    <tr>
                                        <td><?php echo $hotel['id']; ?></td>
                                        <td><?php echo htmlspecialchars($hotel['name']); ?></td>
                                        <td><?php echo htmlspecialchars($hotel['city']); ?></td>
                                        <td>$<?php echo number_format($hotel['price_per_day'], 2); ?></td>
                                        <td><?php echo $hotel['capacity']; ?></td>
                                        <td><?php echo $hotel['rating']; ?> (<?php echo $hotel['total_reviews']; ?>)</td>
                                        <td>
                                            <span class="badge bg-<?php echo $hotel['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($hotel['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $hotel['status']; ?>">
                                                <button type="submit" class="btn btn-sm btn-<?php echo $hotel['status'] == 'active' ? 'warning' : 'success'; ?>">
                                                    <?php echo $hotel['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
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

    <!-- Add Hotel Modal -->
    <div class="modal fade" id="addHotelModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">

                        <div class="mb-3">
                            <label for="name" class="form-label">Hotel Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price_per_day" class="form-label">Price per Day</label>
                                    <input type="number" step="0.01" class="form-control" name="price_per_day" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Capacity</label>
                                    <input type="number" class="form-control" name="capacity" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="amenities" class="form-label">Amenities (comma separated)</label>
                            <input type="text" class="form-control" name="amenities" placeholder="24/7 Care, Play Area, Grooming">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Hotel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
