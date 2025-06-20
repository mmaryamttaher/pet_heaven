<?php
$page_title = "Service Details";
$css_file = "care.css";
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get service details
$service_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$service_id) {
    setMessage('Invalid service selected.', 'error');
    redirect('index.php');
}

$query = "SELECT * FROM services WHERE id = ? AND status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    setMessage('Service not found or not available.', 'error');
    redirect('index.php');
}

// Get related hotels that offer this service
$query = "SELECT * FROM hotels WHERE status = 'active' ORDER BY rating DESC LIMIT 6";
$stmt = $db->prepare($query);
$stmt->execute();
$hotels = $stmt->fetchAll();
?>

<div class="container" style="margin-top: 150px;">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <?php if ($service['image']): ?>
                <img src="<?php echo $service['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($service['name']); ?>" style="height: 300px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h2><?php echo htmlspecialchars($service['name']); ?></h2>
                    
                    <?php if ($service['price']): ?>
                    <div class="mb-3">
                        <span class="badge bg-primary fs-6">Starting from $<?php echo number_format($service['price'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <h4>About This Service</h4>
                    <p><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                    
                    <div class="mt-4">
                        <h4>What's Included:</h4>
                        <ul class="list-unstyled">
                            <?php
                            // Default inclusions based on service type
                            $inclusions = [];
                            switch(strtolower($service['name'])) {
                                case 'pet hosting':
                                    $inclusions = ['24/7 supervision', 'Comfortable accommodation', 'Regular feeding', 'Exercise and playtime', 'Daily updates'];
                                    break;
                                case 'pet care':
                                    $inclusions = ['Health monitoring', 'Medication administration', 'Grooming basics', 'Companionship', 'Emergency care'];
                                    break;
                                case 'pet training':
                                    $inclusions = ['Basic obedience training', 'Behavioral assessment', 'Customized training plan', 'Progress reports', 'Owner guidance'];
                                    break;
                                case 'additional services':
                                    $inclusions = ['Grooming services', 'Veterinary care', 'Special dietary needs', 'Transportation', 'Photography sessions'];
                                    break;
                                default:
                                    $inclusions = ['Professional care', 'Safe environment', 'Regular updates', 'Emergency support'];
                            }
                            
                            foreach ($inclusions as $inclusion): ?>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> <?php echo $inclusion; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Book This Service</h5>
                    <p class="text-muted">Find hotels that offer this service</p>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="search.php" class="btn btn-primary btn-lg w-100 mb-2">Find Hotels</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-lg w-100 mb-2">Login to Book</a>
                    <?php endif; ?>
                    
                    <a href="index.php" class="btn btn-outline-secondary w-100">Back to Home</a>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-body">
                    <h6>Need More Information?</h6>
                    <p class="small text-muted">Have questions about this service?</p>
                    <a href="chat.php" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-comments"></i> Chat with Us
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($hotels)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3>Hotels Offering This Service</h3>
            <div class="row">
                <?php foreach ($hotels as $hotel): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $hotel['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?php echo htmlspecialchars($hotel['name']); ?></h6>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['city']); ?>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-warning small">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $hotel['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </span>
                                    <strong class="text-primary">$<?php echo number_format($hotel['price_per_day'], 2); ?>/day</strong>
                                </div>
                                <a href="hotel.php?id=<?php echo $hotel['id']; ?>" class="btn btn-outline-primary btn-sm w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
