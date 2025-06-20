<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Track terms and conditions views (optional analytics)
if (isLoggedIn()) {
    try {
        // Log terms page view for analytics
        $log_query = "INSERT INTO page_views (user_id, page_name, view_date) VALUES (?, ?, NOW())
                      ON DUPLICATE KEY UPDATE view_count = view_count + 1, last_viewed = NOW()";
        $log_stmt = $db->prepare($log_query);
        $log_stmt->execute([$_SESSION['user_id'], 'terms_conditions']);
    } catch (Exception $e) {
        // Silently fail if page_views table doesn't exist
    }
}

// Handle search functionality
$search_results = [];
$search_query = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['search'])) {
    $search_query = sanitizeInput($_GET['search']);

    // Search within terms and conditions content
    $terms_sections = [
        'App Usage Rights' => 'You\'re not allowed to copy, or modify the app, any part of the app, or our trademarks in any way.',
        'Service Changes' => 'We reserve the right to make changes to the app or to charge for its services, at any time and for any reason.',
        'App Updates' => 'We may wish to update the app. You promise to always accept updates to the application when offered to you.',
        'Service Termination' => 'We may also wish to stop providing the app, and may terminate use of it at any time without giving notice.',
        'Intellectual Property' => 'The app itself, and all the trade marks, copyright, database rights and other intellectual property rights related to it, still belong to us.',
        'User Responsibilities' => 'You should make sure that you read the terms carefully before using the app.',
        'Changes to Terms' => 'We may update our Terms and Conditions from time to time. You are advised to review this page periodically.',
        'Contact Information' => 'If you have any questions or suggestions about our Terms and Conditions, do not hesitate to contact us.'
    ];

    foreach ($terms_sections as $title => $content) {
        if (stripos($title, $search_query) !== false || stripos($content, $search_query) !== false) {
            $search_results[] = [
                'title' => $title,
                'content' => $content,
                'relevance' => (stripos($title, $search_query) !== false) ? 'high' : 'medium'
            ];
        }
    }
}

// Get current terms version and effective date
$terms_version = "1.0";
$effective_date = date('Y-m-d');

// Try to get terms info from database
try {
    $terms_info_query = "SELECT version, effective_date FROM terms_versions ORDER BY created_at DESC LIMIT 1";
    $terms_info_stmt = $db->prepare($terms_info_query);
    $terms_info_stmt->execute();
    $terms_info = $terms_info_stmt->fetch();

    if ($terms_info) {
        $terms_version = $terms_info['version'];
        $effective_date = $terms_info['effective_date'];
    }
} catch (Exception $e) {
    // Table doesn't exist, use defaults
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions</title>
    <link rel="stylesheet" href="terms.css">
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
    <section class="search-results" style="margin: 20px auto; max-width: 800px; padding: 20px;">
        <h3>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
        <?php foreach ($search_results as $result): ?>
        <div class="result-item" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
            <h5 style="color: #491503;"><?php echo htmlspecialchars($result['title']); ?></h5>
            <p><?php echo htmlspecialchars($result['content']); ?></p>
            <small class="text-muted">Relevance: <?php echo ucfirst($result['relevance']); ?></small>
        </div>
        <?php endforeach; ?>
    </section>
    <?php elseif (!empty($search_query)): ?>
    <section class="no-results" style="margin: 20px auto; max-width: 800px; padding: 20px; text-align: center;">
        <h4>No results found for "<?php echo htmlspecialchars($search_query); ?>"</h4>
        <p>Try searching for terms like "app", "updates", "rights", or "contact"</p>
    </section>
    <?php endif; ?>

    <main>
        <h6><a href="help center.php">Help home ></a><a href="terms.php">Terms & Conditions</a></h6>
        <h1>Terms & Conditions</h1>

        <div style="margin-bottom: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
            <small class="text-muted">
                <strong>Version:</strong> <?php echo htmlspecialchars($terms_version); ?> |
                <strong>Effective Date:</strong> <?php echo htmlspecialchars($effective_date); ?> |
                <strong>Last Updated:</strong> <?php echo date('Y-m-d'); ?>
            </small>
        </div>

        <section class="instructions">
            <p>By downloading or using the app, these terms will automatically apply to you – you should make sure therefore that you read them carefully before using the app. You're not allowed to copy, or modify the app, any part of the app, or our trademarks in any way. You're not allowed to attempt to extract the source code of the app, and you also shouldn't try to translate the app into other languages, or make derivative versions. The app itself, and all the trade marks, copyright, database rights and other intellectual property rights related to it, still belong to Pet Shop.</p>

            <p>Pet Shop is committed to ensuring that the app is as useful and efficient as possible. For that reason, we reserve the right to make changes to the app or to charge for its services, at any time and for any reason. We will never charge you for the app or its services without making it very clear to you exactly what you're paying for.</p>

            <p>At some point, we may wish to update the app. The app is currently available on Android & Web – the requirements for the system (and for any additional systems we decide to extend the availability of the app to) may change, and you'll need to download the updates if you want to keep using the app. Pet Shop does not promise that it will always update the app so that it is relevant to you and/or works with the version that you have installed on your device. However, you promise to always accept updates to the application when offered to you. We may also wish to stop providing the app, and may terminate use of it at any time without giving notice of termination to you. Unless we tell you otherwise, upon any termination, (a) the rights and licenses granted to you in these terms will end; (b) you must stop using the app, and (if needed) delete it from your device.</p>

            <h2>User Account and Responsibilities</h2>
            <p>When you create an account with us, you must provide information that is accurate, complete, and current at all times. You are responsible for safeguarding the password and for all activities that occur under your account. You agree not to disclose your password to any third party.</p>

            <h2>Pet Care Services</h2>
            <p>Our platform connects pet owners with pet care providers. We do not directly provide pet care services. All arrangements are made between users. We recommend that you verify the credentials and references of any pet care provider before engaging their services.</p>

            <h2>Payment and Fees</h2>
            <p>Payment for services is processed through our secure payment system. We may charge service fees for facilitating transactions between users. All fees will be clearly disclosed before any transaction is completed.</p>

            <h2>Liability and Insurance</h2>
            <p>While we strive to connect you with reliable pet care providers, Pet Shop is not liable for any damages, injuries, or losses that may occur during pet care services. We strongly recommend that all users maintain appropriate insurance coverage.</p>

            <h2>Changes to This Terms and Conditions</h2>
            <p>We may update our Terms and Conditions from time to time. Thus, you are advised to review this page periodically for any changes. We will notify you of any changes by posting the new Terms and Conditions on this page. These changes are effective immediately after they are posted on this page.</p>

            <h2>Contact Us</h2>
            <p>If you have any questions or suggestions about our Terms and Conditions, do not hesitate to contact us at:</p>
            <ul>
                <li>Email: legal@petshop.com</li>
                <li>Phone: +20 123 456 7890</li>
                <li>Address: 123 Pet Street, Cairo, Egypt</li>
            </ul>
        </section>
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
