<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Get hotels in Giza
$query = "SELECT * FROM hotels WHERE status = 'active' AND city LIKE '%Giza%' ORDER BY rating DESC, price_per_day ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$hotels = $stmt->fetchAll();

// Handle search
$search_query = '';
$search_results = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['search'])) {
    $search_query = sanitizeInput($_GET['search']);
    $search_query_param = "SELECT * FROM hotels WHERE status = 'active' AND city LIKE '%Giza%' AND (name LIKE ? OR description LIKE ?) ORDER BY rating DESC";
    $search_stmt = $db->prepare($search_query_param);
    $search_param = "%$search_query%";
    $search_stmt->execute([$search_param, $search_param]);
    $search_results = $search_stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cairo</title>
    <link rel="stylesheet" href="giza.css">
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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

<!--start main-->
<section id="main">

</section> <br>
<!--end main-->

<!--start search-->
<form class="d-flex " role="search " style="width: 80%; margin: auto;" method="GET">
    <input class="form-control me-2" type="search" name="search" placeholder="Search" aria-label="Search" value="<?php echo htmlspecialchars($search_query); ?>">
    <button class="btn " type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
<!--end search-->

<!--start hotels-->
<section class="hotels" id="hotels">
    <div id="popularcities">
        <h3>Search resault: Giza</h3>
    </div>
    <div  id="cards">
        <?php
        $display_hotels = !empty($search_results) ? $search_results : $hotels;

        if (empty($display_hotels)):
            // Show default hotels if no data from database
            ?>
            <div class="card ">
                <div class="card-img">
                  <img src="imgs/2024-08-26.webp" alt="h1" >
                </div>
                <div class="details">
                    <h4>Eastwind</h4>
                    <p>26th of July Corridor, Kerdasa, Giza Governorate 12577</p>
                    <div class="next">
                     <p>1400 per day</p>
                     <a href="eastwind.php"><button class="btn btn-dark">>></button></a>
                    </div>
                </div>
            </div>

            <div class="card ">
                <div class="card-img">
                  <img src="imgs/cat13.jpg" alt="h1" >
                </div>
                <div class="details">
                    <h4>Golden Paws</h4>
                    <p>Elward st,giza,giza government</p>
                    <div class="next">
                     <p>900 per day</p>
                     <a href="golden.php"><button class="btn btn-dark">>></button></a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($display_hotels as $hotel): ?>
            <div class="card ">
                <div class="card-img">
                  <img src="<?php echo $hotel['image']; ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" >
                </div>
                <div class="details">
                    <h4><?php echo htmlspecialchars($hotel['name']); ?></h4>
                    <p><?php echo htmlspecialchars($hotel['address']); ?></p>
                    <div class="next">
                     <p><?php echo number_format($hotel['price_per_day'], 0); ?> per day</p>
                     <a href="hotel.php?id=<?php echo $hotel['id']; ?>"><button class="btn btn-dark">>></button></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

</section>

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
