<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle service actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $price = !empty($_POST['price']) ? floatval($_POST['price']) : null;
                $image = sanitizeInput($_POST['image']);
                
                $query = "INSERT INTO services (name, description, price, image) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$name, $description, $price, $image])) {
                    setMessage('Service added successfully!', 'success');
                } else {
                    setMessage('Error adding service.', 'error');
                }
                break;
                
            case 'toggle_status':
                $service_id = intval($_POST['service_id']);
                $current_status = $_POST['current_status'];
                $new_status = $current_status == 'active' ? 'inactive' : 'active';
                
                $query = "UPDATE services SET status = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$new_status, $service_id])) {
                    setMessage('Service status updated successfully!', 'success');
                } else {
                    setMessage('Error updating service status.', 'error');
                }
                break;
                
            case 'update_price':
                $service_id = intval($_POST['service_id']);
                $price = floatval($_POST['price']);
                
                $query = "UPDATE services SET price = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$price, $service_id])) {
                    setMessage('Service price updated successfully!', 'success');
                } else {
                    setMessage('Error updating service price.', 'error');
                }
                break;
        }
        redirect('services.php');
    }
}

// Get all services
$query = "SELECT * FROM services ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$services = $stmt->fetchAll();

$page_title = "Manage Services";
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
                            <a class="nav-link active" href="services.php">
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
                    <h1 class="h2">Manage Services</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus"></i> Add Service
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

                <!-- Services Grid -->
                <div class="row">
                    <?php foreach ($services as $service): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <?php if ($service['image']): ?>
                            <img src="../<?php echo $service['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($service['name']); ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($service['description'], 0, 100)); ?>...</p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-<?php echo $service['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($service['status']); ?>
                                        </span>
                                        <?php if ($service['price']): ?>
                                            <strong class="text-primary">$<?php echo number_format($service['price'], 2); ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPriceModal<?php echo $service['id']; ?>">
                                            Edit Price
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $service['status']; ?>">
                                            <button type="submit" class="btn btn-sm btn-<?php echo $service['status'] == 'active' ? 'warning' : 'success'; ?>">
                                                <?php echo $service['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Price Modal for each service -->
                    <div class="modal fade" id="editPriceModal<?php echo $service['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Price - <?php echo htmlspecialchars($service['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="update_price">
                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label for="price" class="form-label">Price</label>
                                            <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $service['price']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Price</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (Optional)</label>
                            <input type="number" step="0.01" class="form-control" name="price">
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image Path</label>
                            <input type="text" class="form-control" name="image" placeholder="imgs/service-image.jpg">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
