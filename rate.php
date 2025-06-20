<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setMessage('Please log in to rate our app.', 'error');
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user's existing rating if any
$existing_rating = null;
try {
    $rating_query = "SELECT * FROM app_ratings WHERE user_id = ?";
    $rating_stmt = $db->prepare($rating_query);
    $rating_stmt->execute([$_SESSION['user_id']]);
    $existing_rating = $rating_stmt->fetch();
} catch (Exception $e) {
    // Table might not exist, will be created later
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = (int)$_POST['rating'];
    $review = sanitizeInput($_POST['review']);
    
    if ($rating < 1 || $rating > 5) {
        setMessage('Please select a valid rating (1-5 stars).', 'error');
    } else {
        try {
            // Insert or update rating
            $upsert_query = "INSERT INTO app_ratings (user_id, rating, review, created_at) 
                             VALUES (?, ?, ?, NOW()) 
                             ON DUPLICATE KEY UPDATE 
                             rating = VALUES(rating), 
                             review = VALUES(review), 
                             updated_at = NOW()";
            $upsert_stmt = $db->prepare($upsert_query);
            
            if ($upsert_stmt->execute([$_SESSION['user_id'], $rating, $review])) {
                $action = $existing_rating ? 'updated' : 'submitted';
                setMessage("Thank you! Your rating has been $action successfully.", 'success');
                redirect('rate.php');
            } else {
                setMessage('Error saving your rating. Please try again.', 'error');
            }
        } catch (Exception $e) {
            setMessage('Error saving rating: ' . $e->getMessage(), 'error');
        }
    }
}

// Get rating statistics
$rating_stats = [
    'total_ratings' => 0,
    'average_rating' => 0,
    'rating_distribution' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]
];

try {
    // Get total ratings and average
    $stats_query = "SELECT COUNT(*) as total, AVG(rating) as average FROM app_ratings";
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch();
    
    if ($stats) {
        $rating_stats['total_ratings'] = $stats['total'];
        $rating_stats['average_rating'] = round($stats['average'], 1);
    }
    
    // Get rating distribution
    $dist_query = "SELECT rating, COUNT(*) as count FROM app_ratings GROUP BY rating";
    $dist_stmt = $db->prepare($dist_query);
    $dist_stmt->execute();
    $distribution = $dist_stmt->fetchAll();
    
    foreach ($distribution as $dist) {
        $rating_stats['rating_distribution'][$dist['rating']] = $dist['count'];
    }
} catch (Exception $e) {
    // Use default stats if table doesn't exist
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rate Our App</title>
  <link rel="stylesheet" href="rate.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    .emoji {
      font-size: 3rem;
      margin: 20px 0;
      display: flex;
      justify-content: center;
      gap: 15px;
    }
    .emoji span {
      cursor: pointer;
      transition: transform 0.2s;
      padding: 10px;
      border-radius: 50%;
    }
    .emoji span:hover {
      transform: scale(1.2);
      background-color: #f0f0f0;
    }
    .emoji span.selected {
      background-color: #491503;
      color: white;
      transform: scale(1.3);
    }
    .container {
      max-width: 600px;
      margin: 150px auto 50px;
      padding: 40px;
      text-align: center;
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    textarea {
      width: 100%;
      min-height: 120px;
      margin: 20px 0;
      padding: 15px;
      border: 2px solid #ddd;
      border-radius: 10px;
      font-size: 16px;
      resize: vertical;
    }
    button {
      background-color: #491503;
      color: white;
      border: none;
      padding: 12px 30px;
      font-size: 18px;
      border-radius: 25px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    button:hover {
      background-color: #5a1a04;
    }
    .rating-stats {
      margin-top: 40px;
      padding: 20px;
      background-color: #f8f9fa;
      border-radius: 10px;
    }
  </style>
</head>
<body>
  <?php
  // Display messages
  $message = getMessage();
  if ($message):
  ?>
  <div class="alert alert-<?php echo $message['type'] == 'error' ? 'danger' : $message['type']; ?> alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1000; width: 90%; max-width: 500px;">
      <?php echo $message['message']; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="container">
    <h1>Rate our app</h1>
    
    <?php if ($existing_rating): ?>
    <div class="alert alert-info">
      <i class="fas fa-info-circle"></i>
      You previously rated our app <?php echo $existing_rating['rating']; ?> stars. You can update your rating below.
    </div>
    <?php endif; ?>
    
    <form method="POST" id="ratingForm">
      <div class="emoji">
        <span data-rating="5" title="Excellent">😊</span>
        <span data-rating="4" title="Good">😄</span>
        <span data-rating="3" title="Average">🙂</span>
        <span data-rating="2" title="Poor">🙄</span>
        <span data-rating="1" title="Very Poor">☹️</span>
      </div>
      
      <input type="hidden" name="rating" id="selectedRating" value="<?php echo $existing_rating ? $existing_rating['rating'] : ''; ?>" required>
      
      <textarea name="review" placeholder="Write your review" maxlength="500"><?php echo $existing_rating ? htmlspecialchars($existing_rating['review']) : ''; ?></textarea>
      
      <div style="margin: 20px 0;">
        <button type="submit"><?php echo $existing_rating ? 'Update Rating' : 'Submit Rating'; ?></button>
        <a href="user.php" style="margin-left: 15px; color: #491503; text-decoration: none;">← Back to Profile</a>
      </div>
    </form>
    
    <?php if ($rating_stats['total_ratings'] > 0): ?>
    <div class="rating-stats">
      <h3>App Ratings Overview</h3>
      <div class="row">
        <div class="col-md-6">
          <h4><?php echo $rating_stats['average_rating']; ?> <i class="fas fa-star" style="color: #ffc107;"></i></h4>
          <p><?php echo $rating_stats['total_ratings']; ?> total ratings</p>
        </div>
        <div class="col-md-6">
          <div class="rating-breakdown">
            <?php for ($i = 5; $i >= 1; $i--): ?>
            <div class="d-flex align-items-center mb-1">
              <span><?php echo $i; ?> <i class="fas fa-star" style="color: #ffc107; font-size: 12px;"></i></span>
              <div class="progress mx-2" style="flex: 1; height: 8px;">
                <div class="progress-bar" style="width: <?php echo $rating_stats['total_ratings'] > 0 ? ($rating_stats['rating_distribution'][$i] / $rating_stats['total_ratings']) * 100 : 0; ?>%; background-color: #491503;"></div>
              </div>
              <span style="font-size: 12px;"><?php echo $rating_stats['rating_distribution'][$i]; ?></span>
            </div>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <script>
    // Handle emoji rating selection
    document.querySelectorAll('.emoji span').forEach(emoji => {
      emoji.addEventListener('click', function() {
        // Remove selected class from all emojis
        document.querySelectorAll('.emoji span').forEach(e => e.classList.remove('selected'));
        
        // Add selected class to clicked emoji
        this.classList.add('selected');
        
        // Set the rating value
        document.getElementById('selectedRating').value = this.getAttribute('data-rating');
        
        // Update button text based on selection
        const rating = parseInt(this.getAttribute('data-rating'));
        const button = document.querySelector('button[type="submit"]');
        const isUpdate = <?php echo $existing_rating ? 'true' : 'false'; ?>;
        
        if (isUpdate) {
          button.textContent = 'Update Rating';
        } else {
          button.textContent = 'Submit Rating';
        }
      });
    });
    
    // Pre-select existing rating if any
    <?php if ($existing_rating): ?>
    const existingRating = <?php echo $existing_rating['rating']; ?>;
    const existingEmoji = document.querySelector(`[data-rating="${existingRating}"]`);
    if (existingEmoji) {
      existingEmoji.classList.add('selected');
    }
    <?php endif; ?>
    
    // Form validation
    document.getElementById('ratingForm').addEventListener('submit', function(e) {
      const rating = document.getElementById('selectedRating').value;
      if (!rating) {
        e.preventDefault();
        alert('Please select a rating by clicking on one of the emojis.');
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
