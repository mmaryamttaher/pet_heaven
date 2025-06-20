<?php
require_once 'config/config.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('user.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    if (empty($email)) {
        setMessage('Please enter your email address.', 'error');
    } else {
        // Check if email exists in database
        $query = "SELECT id, name, email FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate a password reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
            
            // Store reset token in database (you might want to create a password_resets table)
            // For now, we'll store it in the session for simplicity
            $_SESSION['reset_token'] = $reset_token;
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_expires'] = $reset_expires;
            
            // In a real application, you would send an email here
            // For demo purposes, we'll just redirect to password reset page
            setMessage('Password reset instructions have been sent to your email.', 'success');
            redirect('password.php?token=' . $reset_token);
        } else {
            // Don't reveal if email exists or not for security
            setMessage('If this email is registered, you will receive password reset instructions.', 'info');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
    <link rel="stylesheet" href="forget.css">
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

    <form method="POST" id="forgetPasswordForm">
        <div class="img"><img src="imgs/0fd23087-d8b8-47d2-8a42-c7ebc0704007.jpg" alt="error"></div>
        <h1>Forget Password</h1>
        <p>Provide your account's email for which you want to reset your password</p>
        <div class="logo-input">
            <i class="fa-regular fa-envelope"></i>
            <input type="email" name="email" placeholder="E-mail" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div><br>
        <button type="submit">Next</button>
        
        <div style="text-align: center; margin-top: 20px;">
            <p><a href="login.php" style="color: #491503; text-decoration: none;">← Back to Login</a></p>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 