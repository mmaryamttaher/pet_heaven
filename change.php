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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($new_password) || empty($confirm_password)) {
        setMessage('Please fill in both password fields.', 'error');
    } elseif (strlen($new_password) < 8) {
        setMessage('Password must be at least 8 characters long.', 'error');
    } elseif ($new_password !== $confirm_password) {
        setMessage('Passwords do not match.', 'error');
    } else {
        // Update password in database
        $email = $_SESSION['reset_email'] ?? '';
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $query = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$hashed_password, $email])) {
            // Clear reset session data
            unset($_SESSION['reset_token']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_expires']);
            unset($_SESSION['reset_method']);
            
            setMessage('Password updated successfully! You can now log in with your new password.', 'success');
            redirect('pass update.php');
        } else {
            setMessage('Error updating password. Please try again.', 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change password</title>
  <link rel="stylesheet" href="change.css">
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

  <div class="Change">
    <h1>
        <span class="line1">Create a</span>
        <span class="line2">new password</span>
    </h1>
    <p>Your new password must not be the same or contain the password as your previous ones</p> <br>
    
    <form method="POST" id="changePasswordForm">
      <label for="new_password">New password</label>
      <div class="password-content">
        <input type="password" name="new_password" id="new_password" placeholder="Type a password" required minlength="8">
        <i id="eye1" class="fas fa-eye toggle-password" style="color: #050505;" onclick="togglePassword('new_password', 'eye1')"></i>
      </div>
      <p>Choose a password at least 8 characters</p>
      
      <label for="confirm_password">Confirm new password</label>
      <div class="password-container">
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Type a password" required minlength="8">
        <i id="eye2" class="fas fa-eye toggle-password" style="color: #050505;" onclick="togglePassword('confirm_password', 'eye2')"></i>
      </div>

      <button type="submit">Change password</button>
      
      <div style="text-align: center; margin-top: 20px;">
        <p><a href="password.php?token=<?php echo htmlspecialchars($token); ?>" style="color: #491503; text-decoration: none;">← Back to Method Selection</a></p>
      </div>
    </form>
  </div>

  <script>
    function togglePassword(inputId, eyeId) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById(eyeId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    // Password strength validation
    document.getElementById('new_password').addEventListener('input', function() {
        const password = this.value;
        const strengthText = document.querySelector('.password-content p');
        
        if (password.length < 8) {
            strengthText.style.color = 'red';
            strengthText.textContent = 'Password must be at least 8 characters';
        } else if (password.length >= 8 && password.length < 12) {
            strengthText.style.color = 'orange';
            strengthText.textContent = 'Password strength: Medium';
        } else {
            strengthText.style.color = 'green';
            strengthText.textContent = 'Password strength: Strong';
        }
    });

    // Confirm password validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('new_password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword && password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
