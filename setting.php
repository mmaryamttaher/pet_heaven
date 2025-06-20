<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setMessage('Please log in to access settings.', 'error');
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user's current settings
$user_settings = [
    'language' => 'English',
    'currency' => 'EGP',
    'country' => 'Egypt',
    'notifications' => 'Enable'
];

try {
    // Try to get user settings from database
    $settings_query = "SELECT * FROM user_settings WHERE user_id = ?";
    $settings_stmt = $db->prepare($settings_query);
    $settings_stmt->execute([$_SESSION['user_id']]);
    $db_settings = $settings_stmt->fetch();
    
    if ($db_settings) {
        $user_settings = [
            'language' => $db_settings['language'] ?? 'English',
            'currency' => $db_settings['currency'] ?? 'EGP',
            'country' => $db_settings['country'] ?? 'Egypt',
            'notifications' => $db_settings['notifications'] ?? 'Enable'
        ];
    }
} catch (Exception $e) {
    // Table might not exist, use defaults
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $language = sanitizeInput($_POST['language']);
    $currency = sanitizeInput($_POST['currency']);
    $country = sanitizeInput($_POST['country']);
    $notifications = sanitizeInput($_POST['notifications']);
    
    try {
        // Update or insert user settings
        $update_query = "INSERT INTO user_settings (user_id, language, currency, country, notifications) 
                         VALUES (?, ?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE 
                         language = VALUES(language), 
                         currency = VALUES(currency), 
                         country = VALUES(country), 
                         notifications = VALUES(notifications)";
        $update_stmt = $db->prepare($update_query);
        
        if ($update_stmt->execute([$_SESSION['user_id'], $language, $currency, $country, $notifications])) {
            $user_settings = [
                'language' => $language,
                'currency' => $currency,
                'country' => $country,
                'notifications' => $notifications
            ];
            setMessage('Settings updated successfully!', 'success');
        } else {
            setMessage('Error updating settings.', 'error');
        }
    } catch (Exception $e) {
        setMessage('Error updating settings: ' . $e->getMessage(), 'error');
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting</title>
    <link rel="stylesheet" href="setting.css">
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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
                        <a href="booking.php" class="nav-link ">Booking</a>
                    </li>
                    <li class="navbar-item">
                        <a href="#about" class="nav-link">About Us</a>
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

<!--start setting-->
<section id="settinglist">
    <h1 style="text-align: center; ">Settings</h1>
    
    <form method="POST" id="settingsForm">
        <div class="bar">
            <details>
                <summary>Languages</summary>
                <ol style="list-style: circle;">
                    <li>
                        <label>
                            <input type="radio" name="language" value="Arabic" <?php echo $user_settings['language'] == 'Arabic' ? 'checked' : ''; ?>>
                            Arabic
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="language" value="English" <?php echo $user_settings['language'] == 'English' ? 'checked' : ''; ?>>
                            English
                        </label>
                    </li>
                </ol>
            </details>
        </div> <hr>
        
        <div class="bar">
            <details>
                <summary>Currency</summary>
                <ol style="list-style: circle;">
                    <li>
                        <label>
                            <input type="radio" name="currency" value="EGP" <?php echo $user_settings['currency'] == 'EGP' ? 'checked' : ''; ?>>
                            EGP
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="currency" value="SAR" <?php echo $user_settings['currency'] == 'SAR' ? 'checked' : ''; ?>>
                            SAR
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="currency" value="CHF" <?php echo $user_settings['currency'] == 'CHF' ? 'checked' : ''; ?>>
                            CHF
                        </label>
                    </li>
                </ol>
            </details>
        </div> <hr>
        
        <div class="bar">
            <details>
                <summary>Notification</summary>
                <ol style="list-style: circle;">
                    <li>
                        <label>
                            <input type="radio" name="notifications" value="Enable" <?php echo $user_settings['notifications'] == 'Enable' ? 'checked' : ''; ?>>
                            Enable
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="notifications" value="Disable" <?php echo $user_settings['notifications'] == 'Disable' ? 'checked' : ''; ?>>
                            Disable
                        </label>
                    </li>
                </ol>
            </details>
        </div> <hr>
        
        <div class="bar">
            <details>
                <summary>Country</summary>
                <ol style="list-style: circle;">
                    <li>
                        <label>
                            <input type="radio" name="country" value="Egypt" <?php echo $user_settings['country'] == 'Egypt' ? 'checked' : ''; ?>>
                            Egypt
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="country" value="Saudi Arabia" <?php echo $user_settings['country'] == 'Saudi Arabia' ? 'checked' : ''; ?>>
                            Saudi Arabia
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="country" value="Switzerland" <?php echo $user_settings['country'] == 'Switzerland' ? 'checked' : ''; ?>>
                            Switzerland
                        </label>
                    </li>
                </ol>
            </details>
        </div> <hr>
        
        <div style="text-align: center; margin: 20px 0;">
            <button type="submit" class="btn btn-primary" style="margin-right: 10px;">Save Settings</button>
            <button type="reset" class="btn btn-secondary" style="margin-right: 10px;">Reset</button>
            <a href="user.php" class="btn btn-outline-secondary">Back to Profile</a>
        </div>
    </form>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="setting.php?logout=1" onclick="return confirm('Are you sure you want to log out?')" style="color: #dc3545; text-decoration: none; font-weight: bold;">
            <i class="fa-solid fa-sign-out-alt"></i> Log Out
        </a>
    </div>
</section>
<!--end setting-->

<!--start footer-->
<footer id="contact">
    <img src="imgs/Preview-removebg-preview.png" alt="logo" height="100" width="150">
    <div class="icons">
        <a href=""><i class="fa-brands fa-facebook" style="color: #2958a8;"></i></a>
        <a href=""><i class="fa-brands fa-instagram" style="color: #e713a4;"></i></a>
        <a href=""><i class="fa-brands fa-whatsapp" style="color: #398f00;"></i></a>
        <a href="chat.php"><i class="fa-regular fa-comments"></i></a>
    </div>
</footer>
<!--end footer-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
