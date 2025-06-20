<?php
require_once 'config/config.php';

// Check if reset token is valid
$token = $_GET['token'] ?? '';
$valid_token = false;

if ($token && isset($_SESSION['reset_token']) && $_SESSION['reset_token'] === $token) {
    // Check if token hasn't expired
    if (isset($_SESSION['reset_expires']) && strtotime($_SESSION['reset_expires']) > time()) {
        $valid_token = true;
    }
}

if (!$valid_token) {
    setMessage('Invalid or expired reset token. Please try again.', 'error');
    redirect('forget.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user information for display
$email = $_SESSION['reset_email'] ?? '';
$user = null;
if ($email) {
    $query = "SELECT id, name, email, phone FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reset_method = $_POST['reset-method'] ?? '';
    
    if ($reset_method) {
        // Store the selected method in session
        $_SESSION['reset_method'] = $reset_method;
        
        // Redirect to change password page
        redirect('change.php?token=' . $token);
    } else {
        setMessage('Please select a reset method.', 'error');
    }
}

// Mask email and phone for display
$masked_email = $email;
$masked_phone = '';

if ($user) {
    // Mask email: show first 2 chars and domain
    $email_parts = explode('@', $user['email']);
    if (count($email_parts) == 2) {
        $masked_email = substr($email_parts[0], 0, 2) . '***@' . $email_parts[1];
    }
    
    // Mask phone: show first 3 and last 2 digits
    if ($user['phone']) {
        $phone = $user['phone'];
        if (strlen($phone) > 5) {
            $masked_phone = substr($phone, 0, 3) . '**' . substr($phone, -2);
        } else {
            $masked_phone = $phone;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link rel="stylesheet" href="password.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <?php
    // Display messages
    $message = getMessage();
    if ($message):
    ?>
    <div class="alert alert-<?php echo $message['type'] == 'error' ? 'danger' : $message['type']; ?> alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1000; width: 90%; max-width: 500px;">
        <?php echo $message['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="container">
        <h1>Make selection</h1>
        <p>Select which contact details should we use to reset your password</p>
        
        <form method="POST" id="resetMethodForm">
            <?php if ($masked_phone): ?>
            <div class="option">
                <input type="radio" id="sms" name="reset-method" value="sms" checked>
                <label for="sms">via SMS: <?php echo htmlspecialchars($masked_phone); ?></label>
            </div>
            <?php endif; ?>
            
            <div class="option">
                <input type="radio" id="email" name="reset-method" value="email" <?php echo !$masked_phone ? 'checked' : ''; ?>>
                <label for="email">Via mail: <?php echo htmlspecialchars($masked_email); ?></label>
            </div>
            
            <button type="submit" class="btn">Next</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p><a href="forget.php" style="color: #491503; text-decoration: none;">← Back to Email Entry</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
