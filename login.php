<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, name, email, password, role FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            setMessage('Welcome back, ' . $user['name'] . '!', 'success');
            
            // Redirect to admin dashboard if admin, otherwise to home
            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

$page_title = "Login";
$css_file = "login.css";
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
    <div class="login-form">
        <h1>Hi, Welcome Back!👋</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="email">E-mail</label>
            <input type="email" name="email" placeholder="example@gmail.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            
            <label for="password">Password</label>
            <div class="password-container">
                <input type="password" name="password" placeholder="Enter your password" required>
                <i class="fas fa-eye toggle-password" style="color: #050505;"></i>
            </div>
            
            <div class="options">
                <a href="forget.php" class="forgot-password">Forget password?</a>
            </div>
            
            <div class="check">
                <input type="checkbox" name="remember">
                <label for="remember">Remember Me</label>
            </div>

            <button type="submit">Log in</button>
        </form>
        
        <div class="div">
            <span>Or with</span>  
        </div> 
        
        <div class="social-buttons">
            <button class="facebook-btn"><i class="fa-brands fa-facebook-f fa-lg" style="color: #ffffff;"></i> Log in with facebook</button>
            <button class="gmail-btn"><img src="imgs/88e14cc7e7fcbb0e0e09de26cec86c61-removebg-preview.png" alt="" width="20" height="20"> Log in with gmail</button>
        </div>
        
        <p>Don't have an account? <a href="signup.php">Sign up</a></p>
    </div>

    <script>
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
