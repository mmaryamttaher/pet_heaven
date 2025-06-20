<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pets garden</title>
    <link rel="stylesheet" href="hotel1info.css">
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://getbootstrap.com/docs/5.3/assets/css/docs.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
                        <?php if (isLoggedIn()): ?>
                            <a href="user.php" class="nav-link"><i class="fa-regular fa-user"></i> <?php echo $_SESSION['user_name']; ?></a>
                        <?php else: ?>
                            <a href="login.php" class="nav-link"><i class="fa-regular fa-user"></i></a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
<!--end header-->
<section id="bg">

</section>
<!--start info-->
<section id="main">
    <div id="category">
        <a href=""><button class="btn btn-light" style="background-color: #491403de; border-color: #491503; color: #FDF7F4; border-radius: 18px;">About</button></a>
        <a href="hotel1gallery.php"><button class="btn btn-light" style="background-color: transparent; border-color: transparent;">Gallery</button></a>
        <a href="hotel1ser.php"><button class="btn btn-light" style="background-color: transparent; border-color: transparent;">Services</button></a>
    </div> <hr>
    <div id="descreption">
        <h1>HOTEL DESCREPTION</h1>
        <p>
            Set amidst sprawling green landscapes designed for comfort <br> and fun, 
             this unique pet-friendly hotel combines modern <br> amenities with thoughtful
              touches tailored for animals and their <br> owners. It's the perfect haven 
              for a relaxing escape with your <br> furry companions</p>
    </div> 
    <h1>HOTEL SERVICES</h1>
    <div id="services">
        <div>
         <i class="fa-solid fa-paw"></i>
         <label>Pet Care</label>
        </div>
        <div>
         <i class="fa-solid fa-dog"></i>
         <label>Training</label>
        </div>
        <div>
         <i class="fa-solid fa-bowl-food"></i>
         <label>Meals</label>
        </div>
        <div>
         <i class="fa-solid fa-route"></i>
         <label>Outing</label>
        </div>
    </div>
    <div id="last">
        <i class="fa-solid fa-location-dot"></i>109,elshorouk,elwaha street <br>
        <i class="fa-solid fa-phone"></i>+20 120 345 4784 <br>
        <i class="fa-solid fa-calendar-day"></i> <label> Checkin</label>  <br>
        <i class="fa-solid fa-calendar-day"></i> <label> Checkout</label> 
    </div>
    <div id="buttons">
        <a href="cairo.php"><button class="btn btn-light"><<</button></a>
        <a href="book.php?hotel_id=1"><button class="btn btn-light">Book Now</button></a>
    </div>
</section>
<!--end info-->

<!--start footer-->
<footer id="contact">
    <img src="imgs/Preview-removebg-preview.png" alt="logo" height="100" width="150">
    <div id="cicons">
    <h4>𝘊𝘖𝘕𝘛𝘈𝘊𝘛 𝘜𝘚</h4>
    <div class="icons">
        <a href=""><i class="fa-brands fa-facebook" style="color: #2958a8;"></i></a>
        <a href=""><i class="fa-brands fa-instagram" style="color: #e713a4;"></i></a>
        <a href=""><i class="fa-brands fa-whatsapp" style="color: #398f00;"></i></a>
        <a href="chat.php"><i class="fa-regular fa-comments"></i></a>
    </div>
</div>
</footer>
<!--end footer-->
</body>
</html>
