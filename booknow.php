<?php
require_once 'config/config.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get all active hotels for dropdown
$hotels_query = "SELECT id, name, city, price_per_day FROM hotels WHERE status = 'active' ORDER BY name";
$hotels_stmt = $db->prepare($hotels_query);
$hotels_stmt->execute();
$hotels = $hotels_stmt->fetchAll();

// Get user's pets if logged in
$user_pets = [];
if (isLoggedIn()) {
    $pets_query = "SELECT id, name, type FROM pets WHERE user_id = ?";
    $pets_stmt = $db->prepare($pets_query);
    $pets_stmt->execute([$_SESSION['user_id']]);
    $user_pets = $pets_stmt->fetchAll();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isLoggedIn()) {
        setMessage('Please log in to make a booking.', 'error');
        redirect('login.php');
    }
    
    // Process booking form
    $num_pets = sanitizeInput($_POST['num_pets']);
    $room_type = sanitizeInput($_POST['room_type']);
    $hotel_id = sanitizeInput($_POST['hotel_id']);
    $start_date = sanitizeInput($_POST['start_date']);
    $end_date = sanitizeInput($_POST['end_date']);
    $pet_id = sanitizeInput($_POST['pet_id']);
    
    // Calculate total days and cost
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $total_days = $start->diff($end)->days;
    
    if ($total_days <= 0) {
        setMessage('End date must be after start date.', 'error');
    } else {
        // Get hotel price
        $price_query = "SELECT price_per_day FROM hotels WHERE id = ?";
        $price_stmt = $db->prepare($price_query);
        $price_stmt->execute([$hotel_id]);
        $hotel = $price_stmt->fetch();
        
        if ($hotel) {
            $total_amount = $hotel['price_per_day'] * $total_days;
            
            // Generate booking reference
            $booking_reference = 'BK' . date('Ymd') . rand(100, 999);
            
            // Insert booking
            $booking_query = "INSERT INTO bookings (user_id, hotel_id, pet_id, check_in_date, check_out_date, total_days, total_amount, booking_reference, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')";
            $booking_stmt = $db->prepare($booking_query);
            
            if ($booking_stmt->execute([$_SESSION['user_id'], $hotel_id, $pet_id, $start_date, $end_date, $total_days, $total_amount, $booking_reference])) {
                setMessage('Booking created successfully! Reference: ' . $booking_reference, 'success');
                redirect('booking.php');
            } else {
                setMessage('Error creating booking. Please try again.', 'error');
            }
        } else {
            setMessage('Selected hotel not found.', 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Now</title>
    <link rel="stylesheet" href="booknow.css">
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
            <a href="#menue" class="navbar-toggler"  data-bs-toggle=collapse data-bs-target="#menue"  aria-expanded="false" >
                <span class="navbar-toggler-icon "></span>
            </a>
            <nav class="collapse  navbar-collapse justify-content-end " id="menue"  >
                <ul>
                    <li class="nav-item">
                        <a href="index.php" class="nav-link active">HOME</a>
                    </li>
                    <li class="nav-item">
                        <a href="search.php" class="nav-link ">Search</a>
                    </li>
                    <li class="nav-item">
                        <a href="booking.php" class="nav-link ">Booking</a>
                    </li>
                    <li class="nav-item">
                        <a href="#contact" class="nav-link">Contact Us</a>
                    </li>
                    <li class="nav-item">
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
<section id="bookinginfo">
    <form method="POST" id="bookingForm">
        <h1>Room Info</h1>
        
        <?php if (!isLoggedIn()): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Please <a href="login.php">log in</a> to make a booking.
            </div>
        <?php endif; ?>
        
        <?php if (isLoggedIn() && empty($user_pets)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                You need to add a pet first. <a href="user.php">Add Pet</a>
            </div>
        <?php endif; ?>
        
        <label>Number of Pets</label>
        <div class="combo">
            <i class="fa-solid fa-paw" style="color: #491503;"></i>
            <select name="num_pets" class="form-select" aria-label="Number of pets" style="width: 15%; display: inline;" required>
                <option value="">Number</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select>
        </div> <hr>
        
        <?php if (isLoggedIn() && !empty($user_pets)): ?>
        <label>Select Your Pet</label>
        <div class="combo">
            <i class="fa-solid fa-heart" style="color: #491503;"></i>
            <select name="pet_id" class="form-select" aria-label="Select pet" style="width: 25%; display: inline;" required>
                <option value="">Choose Pet</option>
                <?php foreach ($user_pets as $pet): ?>
                    <option value="<?php echo $pet['id']; ?>"><?php echo htmlspecialchars($pet['name']); ?> (<?php echo ucfirst($pet['type']); ?>)</option>
                <?php endforeach; ?>
            </select>
        </div> <hr>
        <?php endif; ?>
        
        <label>Room Type</label>
        <div class="combo">
            <i class="fa-solid fa-door-open" style="color: #491503;"></i>
            <select name="room_type" class="form-select" aria-label="Room type" style="width: 15%; display: inline;" required>
                <option value="">Type</option>
                <option value="single">Single</option>
                <option value="double">Double</option>
            </select>
        </div> <hr>
        
        <label>Hotel Name</label>
        <div class="combo">
            <i class="fa-solid fa-hotel" style="color: #491503;"></i>
            <select name="hotel_id" class="form-select" aria-label="Hotel selection" style="width: 30%; display: inline;" required onchange="updatePrice()">
                <option value="">Hotel</option>
                <?php foreach ($hotels as $hotel): ?>
                    <option value="<?php echo $hotel['id']; ?>" data-price="<?php echo $hotel['price_per_day']; ?>">
                        <?php echo htmlspecialchars($hotel['name']); ?> - <?php echo htmlspecialchars($hotel['city']); ?> (<?php echo number_format($hotel['price_per_day'], 0); ?> EGP/day)
                    </option>
                <?php endforeach; ?>
            </select>
        </div> <hr>
        
        <h1>Reservation Info</h1>
        <div id="info">
            <label>Start Date</label>
            <div class="combo">
                <i class="fa-solid fa-calendar-days" style="color: #491503;"></i>
                <input type="date" name="start_date" required onchange="calculateTotal()" min="<?php echo date('Y-m-d'); ?>">
            </div> <hr>
            
            <label>End Date</label>
            <div class="combo">
                <i class="fa-solid fa-calendar-days" style="color: #491503;"></i>
                <input type="date" name="end_date" required onchange="calculateTotal()" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            </div> <hr>
            
            <label>Total Cost</label>
            <div class="combo">
                <i class="fa-solid fa-hand-holding-dollar" style="color: #491503;"></i>
                <label id="totalCost">0 EGP</label>
            </div> <hr>
        </div>
        
        <div id="book">
            <div>
                <?php if (isLoggedIn() && !empty($user_pets)): ?>
                    <button type="submit" class="btn btn-light">CONFIRM</button>
                <?php else: ?>
                    <button type="button" class="btn btn-light" disabled>CONFIRM</button>
                <?php endif; ?>
            </div>
            <div>
                <button type="reset" class="btn btn-light">Clear</button>
            </div>
        </div>
    </form>
</section>
<!--end info-->

<script>
function updatePrice() {
    calculateTotal();
}

function calculateTotal() {
    const hotelSelect = document.querySelector('select[name="hotel_id"]');
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    const totalCostLabel = document.getElementById('totalCost');
    
    if (hotelSelect.value && startDate && endDate) {
        const selectedOption = hotelSelect.options[hotelSelect.selectedIndex];
        const pricePerDay = parseFloat(selectedOption.getAttribute('data-price'));
        
        const start = new Date(startDate);
        const end = new Date(endDate);
        const timeDiff = end.getTime() - start.getTime();
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        if (daysDiff > 0) {
            const totalCost = pricePerDay * daysDiff;
            totalCostLabel.textContent = totalCost.toLocaleString() + ' EGP (' + daysDiff + ' days)';
        } else {
            totalCostLabel.textContent = '0 EGP';
        }
    } else {
        totalCostLabel.textContent = '0 EGP';
    }
}
</script>

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
