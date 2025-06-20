<?php
$page_title = "Hotel Details";
$css_file = "hotel1info.css";
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get hotel details
$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
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

// Get hotel reviews
$query = "SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.hotel_id = ? ORDER BY r.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute([$hotel_id]);
$reviews = $stmt->fetchAll();
?>

<div class="container" style="margin-top: 150px;">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <img src="<?php echo $hotel['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>" style="height: 400px; object-fit: cover;">
                <div class="card-body">
                    <h2><?php echo htmlspecialchars($hotel['name']); ?></h2>
                    <p class="text-muted mb-3">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['address']); ?>, <?php echo htmlspecialchars($hotel['city']); ?>
                    </p>
                    
                    <div class="mb-3">
                        <span class="text-warning">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $hotel['rating'] ? '' : '-o'; ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <span class="ms-2"><?php echo $hotel['rating']; ?>/5 (<?php echo $hotel['total_reviews']; ?> reviews)</span>
                    </div>
                    
                    <h4>About This Hotel</h4>
                    <p><?php echo nl2br(htmlspecialchars($hotel['description'])); ?></p>
                    
                    <?php if ($hotel['amenities']): ?>
                        <h4>Amenities</h4>
                        <div class="row">
                            <?php foreach (json_decode($hotel['amenities']) as $amenity): ?>
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-check text-success"></i> <?php echo htmlspecialchars($amenity); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($reviews)): ?>
                        <h4 class="mt-4">Recent Reviews</h4>
                        <?php foreach ($reviews as $review): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6><?php echo htmlspecialchars($review['user_name']); ?></h6>
                                        <div>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?> text-warning"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    <small class="text-muted"><?php echo formatDate($review['created_at']); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Booking Information</h5>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Price per day:</span>
                            <strong class="text-primary">$<?php echo number_format($hotel['price_per_day'], 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Capacity:</span>
                            <span><?php echo $hotel['capacity']; ?> pets</span>
                        </div>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="book.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn btn-primary btn-lg w-100 mb-2">Book Now</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-lg w-100 mb-2">Login to Book</a>
                    <?php endif; ?>
                    
                    <a href="search.php" class="btn btn-outline-secondary w-100">Back to Search</a>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-body">
                    <h6>Need Help?</h6>
                    <p class="small text-muted">Have questions about this hotel or need assistance with booking?</p>
                    <a href="chat.php" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-comments"></i> Chat with Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
