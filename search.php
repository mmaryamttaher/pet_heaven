<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Handle search
$search_query = '';
$search_results = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['search'])) {
    $search_query = sanitizeInput($_GET['search']);
    $query = "SELECT * FROM hotels WHERE status = 'active' AND (name LIKE ? OR description LIKE ? OR city LIKE ?) ORDER BY rating DESC";
    $stmt = $db->prepare($query);
    $search_param = "%$search_query%";
    $stmt->execute([$search_param, $search_param, $search_param]);
    $search_results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <link rel="stylesheet" href="search.css">
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
                        <a href="#" class="nav-link ">Search</a>
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

<!--start home-->
<section id="main">

</section>
<!--end home-->
<!--start search-->
<section id="search">
    <form class="d-flex" role="search" method="GET">
        <input class="form-control me-2" type="search" name="search" placeholder="Search" aria-label="Search" value="<?php echo htmlspecialchars($search_query); ?>">
        <button class="btn " type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>

    <?php if (!empty($search_results)): ?>
        <div id="searchresults" style="margin-top: 40px;">
            <h1>Search Results</h1>
            <div class="row">
                <?php foreach ($search_results as $hotel): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $hotel['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['address']); ?>, <?php echo htmlspecialchars($hotel['city']); ?>
                            </p>
                            <p class="card-text"><?php echo htmlspecialchars(substr($hotel['description'], 0, 100)); ?>...</p>

                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $hotel['rating'] ? '' : '-o'; ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                        <small class="text-muted">(<?php echo $hotel['total_reviews']; ?> reviews)</small>
                                    </div>
                                    <div class="text-end">
                                        <h5 class="text-primary mb-0"><?php echo number_format($hotel['price_per_day'], 0); ?> EGP</h5>
                                        <small class="text-muted">per day</small>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="hotel.php?id=<?php echo $hotel['id']; ?>" class="btn btn-outline-primary flex-fill">View Details</a>
                                    <a href="book.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn btn-primary flex-fill">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php elseif (!empty($search_query)): ?>
        <div id="noresults" style="margin-top: 40px; text-align: center;">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4>No hotels found</h4>
            <p class="text-muted">Try searching for a different location or hotel name</p>
        </div>
    <?php endif; ?>

    <div id="popularcities">
        <h1>Popular Cities</h1>
        <div class="cities">
            <div class="city">
                <img src="imgs/Cairo-City.jpg" alt="hosting" width="83px" height="85px">
                <a href="cairo.php"><h4>Cairo</h4></a>
            </div>
            <div class="city">
                <img src="imgs/IMG_8266.JPG" alt="petcare" width="83px" height="85px">
                <a href="suez.php"><h4>Suez</h4></a>
            </div>
            <div class="city">
                <img src="imgs/IMG_8267.JPG" alt="training" width="83px" height="85px">
                <a href="giza.php"><h4>Giza</h4></a>
            </div>
        </div>
    </div>
</section>

<section id="recent">
    <div id="recentsearches">
        <h1>Recent searches</h1>
        <div id="recentbar">
         <div>
            <i class="fa-solid fa-clock-rotate-left"></i>
            <input type="text">
         </div>
         <div>
            <i class="fa-solid fa-clock-rotate-left"></i>
            <input type="text">
         </div>
         <div>
            <i class="fa-solid fa-clock-rotate-left"></i>
            <input type="text">
         </div>
         <div>
            <i class="fa-solid fa-clock-rotate-left"></i>
            <input type="text">
         </div>
    </div>
</section>
<!--end search-->

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
