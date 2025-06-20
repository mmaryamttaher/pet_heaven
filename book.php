<?php
$page_title = "Book Hotel";
$css_file = "booknow.css";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setMessage('Please log in to make a booking.', 'error');
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get hotel details
$hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
if (!$hotel_id) {
    setMessage('Invalid hotel selected.', 'error');
    redirect('search.php');
}

$query = "SELECT * FROM hotels WHERE id = ? AND status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    setMessage('Hotel not found or not available.', 'error');
    redirect('search.php');
}

// Get user's pets
$query = "SELECT * FROM pets WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$pets = $stmt->fetchAll();

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = intval($_POST['pet_id']);
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];
    $special_requests = sanitizeInput($_POST['special_requests']);
    
    // Validate dates
    $check_in = new DateTime($check_in_date);
    $check_out = new DateTime($check_out_date);
    $today = new DateTime();
    
    if ($check_in < $today) {
        setMessage('Check-in date cannot be in the past.', 'error');
    } elseif ($check_out <= $check_in) {
        setMessage('Check-out date must be after check-in date.', 'error');
    } else {
        // Calculate total days and amount
        $total_days = $check_in->diff($check_out)->days;
        $total_amount = $total_days * $hotel['price_per_day'];
        
        // Generate booking reference
        $booking_reference = 'BK' . date('Ymd') . rand(1000, 9999);
        
        // Insert booking
        $query = "INSERT INTO bookings (user_id, hotel_id, pet_id, check_in_date, check_out_date, total_days, total_amount, special_requests, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$_SESSION['user_id'], $hotel_id, $pet_id, $check_in_date, $check_out_date, $total_days, $total_amount, $special_requests, $booking_reference])) {
            setMessage('Booking created successfully! Booking reference: ' . $booking_reference, 'success');
            redirect('booking.php');
        } else {
            setMessage('Error creating booking. Please try again.', 'error');
        }
    }
}
?>

<div class="container" style="margin-top: 150px;">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Book Your Stay</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($pets)): ?>
                        <div class="alert alert-warning">
                            <h5>No pets found!</h5>
                            <p>You need to add at least one pet before making a booking.</p>
                            <a href="add_pet.php" class="btn btn-primary">Add Pet</a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="pet_id" class="form-label">Select Pet</label>
                                <select class="form-select" name="pet_id" required>
                                    <option value="">Choose your pet</option>
                                    <?php foreach ($pets as $pet): ?>
                                    <option value="<?php echo $pet['id']; ?>">
                                        <?php echo htmlspecialchars($pet['name']); ?> (<?php echo ucfirst($pet['type']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="check_in_date" class="form-label">Check-in Date</label>
                                        <input type="date" class="form-control" name="check_in_date" required min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="check_out_date" class="form-label">Check-out Date</label>
                                        <input type="date" class="form-control" name="check_out_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="special_requests" class="form-label">Special Requests (Optional)</label>
                                <textarea class="form-control" name="special_requests" rows="3" placeholder="Any special care instructions or requests..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">Book Now</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Hotel Summary -->
            <div class="card">
                <img src="<?php echo $hotel['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                    <p class="card-text">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['address']); ?>, <?php echo htmlspecialchars($hotel['city']); ?>
                    </p>
                    <p class="card-text"><?php echo htmlspecialchars($hotel['description']); ?></p>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Price per day:</span>
                            <strong>$<?php echo number_format($hotel['price_per_day'], 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Capacity:</span>
                            <span><?php echo $hotel['capacity']; ?> pets</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Rating:</span>
                            <span>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= $hotel['rating'] ? '' : '-o'; ?> text-warning"></i>
                                <?php endfor; ?>
                                (<?php echo $hotel['total_reviews']; ?>)
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($hotel['amenities']): ?>
                        <h6>Amenities:</h6>
                        <ul class="list-unstyled">
                            <?php foreach (json_decode($hotel['amenities']) as $amenity): ?>
                                <li><i class="fas fa-check text-success"></i> <?php echo htmlspecialchars($amenity); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate total amount when dates change
document.addEventListener('DOMContentLoaded', function() {
    const checkInInput = document.querySelector('input[name="check_in_date"]');
    const checkOutInput = document.querySelector('input[name="check_out_date"]');
    const pricePerDay = <?php echo $hotel['price_per_day']; ?>;
    
    function updateTotal() {
        if (checkInInput.value && checkOutInput.value) {
            const checkIn = new Date(checkInInput.value);
            const checkOut = new Date(checkOutInput.value);
            const timeDiff = checkOut.getTime() - checkIn.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            if (daysDiff > 0) {
                const total = daysDiff * pricePerDay;
                // You can display the total here if needed
                console.log('Total days:', daysDiff, 'Total amount:', total);
            }
        }
    }
    
    checkInInput.addEventListener('change', updateTotal);
    checkOutInput.addEventListener('change', updateTotal);
});
</script>

<?php require_once 'includes/footer.php'; ?>
