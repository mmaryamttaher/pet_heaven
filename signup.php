<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitizeInput($_POST['phone']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email already exists';
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$name, $email, $hashed_password, $phone])) {
                $success = 'Account created successfully! You can now log in.';
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
    }
}

$page_title = "Sign Up";
$css_file = "signup.css";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo $css_file; ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="signup-form">
        <h1>Create Account</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="name">Full Name</label>
            <input type="text" name="name" placeholder="Enter your full name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            
            <label for="email">E-mail</label>
            <input type="email" name="email" placeholder="example@gmail.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            
            <label for="phone">Phone (Optional)</label>
            <input type="tel" name="phone" placeholder="Enter your phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            
            <label for="password">Password</label>
            <div class="password-container">
                <input type="password" name="password" placeholder="Enter your password" required>
                <i class="fas fa-eye toggle-password" style="color: #050505;"></i>
            </div>
            
            <label for="confirm_password">Confirm Password</label>
            <div class="password-container">
                <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                <i class="fas fa-eye toggle-password" style="color: #050505;"></i>
            </div>
            
            <div class="check">
                <input type="checkbox" name="terms" required>
                <label for="terms">I agree to the <a href="terms.php">Terms and Conditions</a></label>
            </div>

            <button type="submit">Sign Up</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(function(element) {
            element.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>
