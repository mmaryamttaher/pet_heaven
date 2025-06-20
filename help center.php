<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Track help center visits (optional analytics)
if (isLoggedIn()) {
    try {
        $log_query = "INSERT INTO page_views (user_id, page_name, view_date) VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE view_count = view_count + 1, last_viewed = NOW()";
        $log_stmt = $db->prepare($log_query);
        $log_stmt->execute([$_SESSION['user_id'], 'help_center']);
    } catch (Exception $e) {
        // Silently fail if page_views table doesn't exist
    }
}

// Handle search functionality
$search_results = [];
$search_query = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['search'])) {
    $search_query = sanitizeInput($_GET['search']);
    
    // Define help articles and FAQs
    $help_articles = [
        'FAQs' => [
            'How to create an account' => 'Learn how to sign up for a new account on our platform. Visit the signup page and fill in your details.',
            'How to host pets' => 'Discover how to become a pet host and offer your services to pet owners in your area.',
            'How to book a host' => 'Find out how to search for and book a pet host for your furry friends.'
        ],
        'Policies' => [
            'Cancellation policy' => 'Understand our cancellation terms and conditions for bookings and refunds.',
            'Terms of use' => 'Read our complete terms of service and user agreement.',
            'Privacy policy' => 'Learn about how we collect, use, and protect your personal information.'
        ],
        'Technical Support' => [
            'Login issues' => 'Troubleshoot common login problems including forgotten passwords and account lockouts.',
            'Payment problems' => 'Resolve payment issues, failed transactions, and billing questions.',
            'App not working' => 'Fix common app problems, crashes, and performance issues.'
        ]
    ];
    
    // Search through all articles
    foreach ($help_articles as $category => $articles) {
        foreach ($articles as $title => $content) {
            if (stripos($title, $search_query) !== false || stripos($content, $search_query) !== false) {
                $search_results[] = [
                    'category' => $category,
                    'title' => $title,
                    'content' => $content,
                    'relevance' => (stripos($title, $search_query) !== false) ? 'high' : 'medium'
                ];
            }
        }
    }
}

// Get popular help topics from database (if help_topics table exists)
$popular_topics = [];
try {
    $topics_query = "SELECT topic_name, view_count FROM help_topics ORDER BY view_count DESC LIMIT 5";
    $topics_stmt = $db->prepare($topics_query);
    $topics_stmt->execute();
    $popular_topics = $topics_stmt->fetchAll();
} catch (Exception $e) {
    // Table doesn't exist, use default topics
    $popular_topics = [
        ['topic_name' => 'How to create an account', 'view_count' => 150],
        ['topic_name' => 'How to book a host', 'view_count' => 120],
        ['topic_name' => 'Payment problems', 'view_count' => 95],
        ['topic_name' => 'Login issues', 'view_count' => 80],
        ['topic_name' => 'Cancellation policy', 'view_count' => 65]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center</title>
    <link rel="stylesheet" href="help center.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
   <!--start header-->
   <header class="navbar navbar-expand-lg  "  >
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

    <section class="main">
        <div class="content">
            <h1>Search for advice and answers</h1>
            <div class="search-bar">
                <form method="GET" style="display: flex; width: 100%;">
                    <input type="text" name="search" placeholder="I need help with..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1;" />
                    <button type="submit">🔍</button>
                </form>
            </div>
        </div>
    </section>

    <?php if (!empty($search_results)): ?>
    <section class="search-results" style="margin: 20px auto; max-width: 1000px; padding: 20px;">
        <h3>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
        <div class="row">
            <?php foreach ($search_results as $result): ?>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($result['category']); ?></h6>
                        <h5 class="card-title"><?php echo htmlspecialchars($result['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($result['content']); ?></p>
                        <small class="text-muted">Relevance: <?php echo ucfirst($result['relevance']); ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php elseif (!empty($search_query)): ?>
    <section class="no-results" style="margin: 20px auto; max-width: 800px; padding: 20px; text-align: center;">
        <h4>No results found for "<?php echo htmlspecialchars($search_query); ?>"</h4>
        <p>Try searching for terms like "account", "booking", "payment", or "login"</p>
    </section>
    <?php endif; ?>

    <?php if (!empty($popular_topics)): ?>
    <section class="popular-topics" style="margin: 20px auto; max-width: 1000px; padding: 20px;">
        <h3>Popular Help Topics</h3>
        <div class="row">
            <?php foreach ($popular_topics as $topic): ?>
            <div class="col-md-4 mb-2">
                <div class="card" style="border-left: 4px solid #491503;">
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($topic['topic_name']); ?></h6>
                        <small class="text-muted"><?php echo $topic['view_count']; ?> views</small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <main>
        <div class="categories-section">
            <div class="category">
                <h2>FAQs</h2>
                <ul>
                    <li><a href="create acc.php">How to create an account</a></li>
                    <li><a href="host.php">How to host pets</a></li>
                    <li><a href="book.php">How to book a host</a></li>
                </ul>
            </div>
            <div class="category">
                <h2>Policies</h2>
                <ul>
                    <li><a href="cancellation.php">Cancellation policy</a></li>
                    <li><a href="terms.php">Terms of use</a></li>
                    <li><a href="pravicy.php">Privacy policy</a></li>
                </ul>
            </div>
            <div class="category">
                <h2>Technical Support</h2>
                <ul>
                    <li><a href="issuess.php">Login issues</a></li>
                    <li><a href="payment.php">Payment problems</a></li>
                    <li><a href="app.php">App not working</a></li>
                </ul>
            </div>
        </div>
    </main>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
