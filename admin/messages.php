<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle message actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'reply') {
        $user_id = intval($_POST['user_id']);
        $message = sanitizeInput($_POST['message']);
        
        $query = "INSERT INTO messages (user_id, admin_id, message, sender_type) VALUES (?, ?, ?, 'admin')";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$user_id, $_SESSION['user_id'], $message])) {
            setMessage('Reply sent successfully!', 'success');
        } else {
            setMessage('Error sending reply.', 'error');
        }
        redirect('messages.php');
    }
    
    if ($_POST['action'] == 'mark_read') {
        $message_id = intval($_POST['message_id']);
        
        $query = "UPDATE messages SET is_read = 1 WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$message_id])) {
            setMessage('Message marked as read.', 'success');
        } else {
            setMessage('Error updating message.', 'error');
        }
        redirect('messages.php');
    }
}

// Get all messages grouped by user
$query = "SELECT m.*, u.name as user_name, u.email as user_email,
          a.name as admin_name
          FROM messages m 
          JOIN users u ON m.user_id = u.id 
          LEFT JOIN users a ON m.admin_id = a.id 
          ORDER BY m.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$all_messages = $stmt->fetchAll();

// Group messages by user
$conversations = [];
foreach ($all_messages as $message) {
    $user_id = $message['user_id'];
    if (!isset($conversations[$user_id])) {
        $conversations[$user_id] = [
            'user_name' => $message['user_name'],
            'user_email' => $message['user_email'],
            'messages' => []
        ];
    }
    $conversations[$user_id]['messages'][] = $message;
}

$page_title = "Messages";
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
        .message-user {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        .message-admin {
            background-color: #f3e5f5;
            border-left: 4px solid #9c27b0;
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
                            <a class="nav-link" href="services.php">
                                <i class="fas fa-concierge-bell"></i> Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="messages.php">
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
                    <h1 class="h2">Messages</h1>
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

                <?php if (empty($conversations)): ?>
                    <div class="text-center">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h4>No messages yet</h4>
                        <p class="text-muted">Customer messages will appear here</p>
                    </div>
                <?php else: ?>
                    <!-- Conversations -->
                    <?php foreach ($conversations as $user_id => $conversation): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($conversation['user_name']); ?>
                                <small class="text-muted">(<?php echo htmlspecialchars($conversation['user_email']); ?>)</small>
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Messages -->
                            <div class="mb-3" style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($conversation['messages'] as $msg): ?>
                                <div class="p-2 mb-2 rounded <?php echo $msg['sender_type'] == 'user' ? 'message-user' : 'message-admin'; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>
                                                <?php if ($msg['sender_type'] == 'user'): ?>
                                                    <i class="fas fa-user text-primary"></i> <?php echo htmlspecialchars($msg['user_name']); ?>
                                                <?php else: ?>
                                                    <i class="fas fa-user-shield text-purple"></i> <?php echo htmlspecialchars($msg['admin_name']); ?> (Admin)
                                                <?php endif; ?>
                                            </strong>
                                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted"><?php echo formatDateTime($msg['created_at']); ?></small>
                                            <?php if ($msg['sender_type'] == 'user' && !$msg['is_read']): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="mark_read">
                                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Mark Read</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Reply Form -->
                            <form method="POST">
                                <input type="hidden" name="action" value="reply">
                                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                <div class="input-group">
                                    <textarea class="form-control" name="message" rows="2" placeholder="Type your reply..." required></textarea>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
