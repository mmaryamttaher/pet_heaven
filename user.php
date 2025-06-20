<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Get user data if logged in
$user = null;
$pets = [];

if (isLoggedIn()) {
    // Get user data
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Get user's pets
    $pets_query = "SELECT * FROM pets WHERE user_id = ?";
    $pets_stmt = $db->prepare($pets_query);
    $pets_stmt->execute([$_SESSION['user_id']]);
    $pets = $pets_stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="stylesheet" href="user.css">
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
                        <a href="#" class="nav-link"><i class="fa-regular fa-user"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
<!--end header-->

<!--start home-->
<section id="main">

</section>
<!--end home-->

<!--start user menue-->
<section id="usermenue">
    <div id="username">
        <i class="fa-regular fa-user" style="background-color: #f0ebe9; border-radius: 60px; font-size: 60px;"></i>
        <h1><?php echo isLoggedIn() && $user ? htmlspecialchars($user['name']) : '𝒰𝓈𝑒𝓇 𝒩𝒶𝓂𝑒'; ?></h1>
    </div>
    <div id="userlist">
        <div id="pinfo">
            <div id="to">
                <i class="fa-solid fa-address-card" style="color: #491503; font-size: 21px;"></i>
             <h3 style="font-family:Arial, Helvetica, sans-serif;">𝘗𝘦𝘳𝘴𝘰𝘯𝘢𝘭 𝘐𝘯𝘧𝘰</h3>
            </div>
            <a href="personalinfo.php"><button class="btn btn-light"><i class="fa-solid fa-right-long" style="color: #491503; "></i></button></a>
        </div>
        <div id="petinfo">
            <div id="to">
                <i class="fa-solid fa-paw" style="color: #491503; font-size: 21px;"></i>
             <h3>𝘗𝘦𝘵 𝘐𝘯𝘧𝘰</h3>
            </div>
            <a href="pet.php"><button class="btn btn-light"><i class="fa-solid fa-right-long" style="color: #491503;"></i></button></a>
        </div>
        <div id="wallet">
            <div id="to">
                <i class="fa-solid fa-wallet" style="color: #491503; font-size: 21px;"></i>
             <h3>𝘞𝘢𝘭𝘭𝘦𝘵</h3>
            </div>
            <a href="wallet.php"><button class="btn btn-light"><i class="fa-solid fa-right-long" style="color: #491503;"></i></button></a>
        </div>
        <div id="privacypolicy">
            <div id="to">
                <i class="fa-solid fa-shield-halved" style="color: #491503; font-size: 21px;"></i>
             <h3>𝘗𝘳𝘪𝘷𝘢𝘤𝘺 & 𝘱𝘰𝘭𝘪𝘤𝘺</h3>
            </div>
            <a href="pravicy.php"><button class="btn btn-light"><i class="fa-solid fa-right-long" style="color: #491503;"></i></button></a>
        </div>
        <div id="helpcenter">
            <div id="to">
                <i class="fa-solid fa-circle-question" style="color: #491503; font-size: 21px;"></i>
             <h3>𝘏𝘦𝘭𝘱 𝘊𝘦𝘯𝘵𝘦𝘳</h3>
            </div>
            <a href="help center.php"><button class="btn btn-light"><i class="fa-solid fa-right-long" style="color: #491503;"></i></button></a>
        </div>
        <div id="Setting">
            <div id="to">
                <i class="fa-solid fa-gear" style="color: #491503; font-size: 21px;"></i>
             <h3>𝘚𝘦𝘵𝘵𝘪𝘯𝘨𝘴</h3>
            </div>
            <a href="setting.php"><button class="btn btn-light"><i class="fa-solid fa-right-long" style="color: #491503;"></i></button></a>
        </div>
        <div id="terms">
            <div id="to">
                <i class="fa-solid fa-person-circle-exclamation" style="color: #491503; font-size: 21px;"></i>
             <h3>𝘛𝘦𝘳𝘮𝘴 & 𝘊𝘰𝘯𝘥𝘪𝘵𝘪𝘰𝘯𝘴</h3>
            </div>
            <a href="terms.php"><button class="btn btn-light"><i class="fa-solid fa-right-long" style="color: #491503;"></i></button></a>
        </div>
    </div>
    <div id="login">
        <?php if (isLoggedIn()): ?>
            <!-- Show user info and logout if logged in -->
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo htmlspecialchars($user['name']); ?>
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="personalinfo.php">Profile</a></li>
                  <li><a class="dropdown-item" href="booking.php">My Bookings</a></li>
                  <?php if (isAdmin()): ?>
                      <li><a class="dropdown-item" href="admin/dashboard.php">Admin Panel</a></li>
                  <?php endif; ?>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <!-- Show login/signup if not logged in (matches HTML exactly) -->
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    𝙇𝙤𝙜 𝙄𝙣
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="login.php">User</a></li>
                  <li><a class="dropdown-item" href="admin/dashboard.php">Admin</a></li>
                </ul> <a href="signup.php"><button>𝙎𝙞𝙜𝙣 𝙐𝙥</button></a>
              </div>
        <?php endif; ?>
    </div>
    <div id="rate">
        <a href="rate.php"><button style="text-align: center;">𝙍𝙖𝙩𝙚 𝙐𝙨</button></a>
    </div>
</section>
<!--end user menue-->

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
