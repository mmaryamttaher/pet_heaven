<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setMessage('Please log in to view your personal information.', 'error');
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $full_name = trim($first_name . ' ' . $last_name);
    $email = sanitizeInput($_POST['email']);
    $country_code = sanitizeInput($_POST['country_code']);
    $mobile = sanitizeInput($_POST['mobile']);
    $phone = $country_code . $mobile;
    $address = sanitizeInput($_POST['address']);
    
    // Update user information
    $update_query = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
    $update_stmt = $db->prepare($update_query);
    
    if ($update_stmt->execute([$full_name, $email, $phone, $address, $_SESSION['user_id']])) {
        $_SESSION['user_name'] = $full_name;
        setMessage('Personal information updated successfully!', 'success');
        redirect('personalinfo.php');
    } else {
        setMessage('Error updating personal information.', 'error');
    }
}

// Split name into first and last name for display
$name_parts = explode(' ', $user['name'], 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';

// Extract country code and mobile number
$phone = $user['phone'] ?? '';
$country_code = '+20'; // Default
$mobile = '';
if ($phone) {
    if (strpos($phone, '+20') === 0) {
        $country_code = '+20';
        $mobile = substr($phone, 3);
    } elseif (strpos($phone, '+966') === 0) {
        $country_code = '+966';
        $mobile = substr($phone, 4);
    } elseif (strpos($phone, '+39') === 0) {
        $country_code = '+39';
        $mobile = substr($phone, 3);
    } else {
        $mobile = $phone;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal information</title>
    <link rel="stylesheet" href="personalinfo.css">
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

<!--start info-->
<section id="personalinfo">
    <div id="pic">
        <img src="imgs/ec5a095a2c354f3d981bacad02885f33.jpg"  alt="profile" style="border-radius: 60px;" width="70px" height="70px">
    </div>
    <form method="POST" id="personalInfoForm">
        <div id="info">
            <label>Full Name</label>
            <div class="combo d-flex" >
                <i class="fa-solid fa-user" style="color: #491503; padding-right: 10px;"></i>
                <div class="row" >
                    <div class="col">
                      <input type="text" name="first_name" class="form-control" placeholder="First name" aria-label="First name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                    </div>
                    <div class="col">
                      <input type="text" name="last_name" class="form-control" placeholder="Last name" aria-label="Last name" value="<?php echo htmlspecialchars($last_name); ?>">
                    </div>
                  </div>
            </div> <hr>
            <label for="exampleFormControlInput1" class="form-label">Email address</label>
            <div class="combo d-flex">
                <i class="fa-solid fa-envelope" style="color: #491503; padding-right: 10px;"></i>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" id="exampleFormControlInput1" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                  </div>
            </div> <hr>
            <label>Mobile Number</label>
            <div class="combo">
                <i class="fa-solid fa-mobile-screen-button" style="color: #491503;"></i>
                    
                    <select name="country_code" class="form-select" aria-label="Country code" style="width: 6%; display: inline;">
                        <option value="+20" <?php echo $country_code == '+20' ? 'selected' : ''; ?>>+20</option>
                        <option value="+966" <?php echo $country_code == '+966' ? 'selected' : ''; ?>>+966</option>
                        <option value="+39" <?php echo $country_code == '+39' ? 'selected' : ''; ?>>+39</option>
                      </select>
                      <input type="text" name="mobile" class="form-control" id="mobile" placeholder="MOBILE NUMBER" style="width: 15%; display: inline;" value="<?php echo htmlspecialchars($mobile); ?>">
            </div> <hr>
            <label for="address">Address</label>
            <div class="combo d-flex">
                <i class="fa-solid fa-location-dot" style="color: #491503; padding-right: 10px;"></i>
                <div class="mb-3">
                    <input type="text" name="address" class="form-control" id="address" placeholder="YOUR ADDRESS" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                  </div>
            </div> <hr>
        </div>
        <div id="buttons">
            <div >
                <button type="submit" class="btn btn-light">Save</button>
            </div>
            <div >
                <button type="reset" class="btn btn-light">Reset</button>
            </div>
        </div>
    </form>
</section>
<!--end info-->

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
