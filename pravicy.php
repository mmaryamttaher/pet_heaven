<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Track privacy policy views (optional analytics)
if (isLoggedIn()) {
    try {
        // Log privacy policy view for analytics
        $log_query = "INSERT INTO page_views (user_id, page_name, view_date) VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE view_count = view_count + 1, last_viewed = NOW()";
        $log_stmt = $db->prepare($log_query);
        $log_stmt->execute([$_SESSION['user_id'], 'privacy_policy']);
    } catch (Exception $e) {
        // Silently fail if page_views table doesn't exist
    }
}

// Handle search functionality
$search_results = [];
$search_query = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['search'])) {
    $search_query = sanitizeInput($_GET['search']);
    
    // Search within privacy policy content
    $privacy_sections = [
        'Information Collection and Use' => 'For a better experience, while using our Service, we may require you to provide us with certain personally identifiable information.',
        'Log Data' => 'We collect data and information on your phone called Log Data. This Log Data may include information such as your device Internet Protocol address.',
        'Cookies' => 'Cookies are files with a small amount of data that are commonly used as anonymous unique identifiers.',
        'Service Providers' => 'We may employ third-party companies and individuals to facilitate our Service.',
        'Security' => 'We value your trust in providing us your Personal Information, thus we are striving to use commercially acceptable means of protecting it.',
        'Children\'s Privacy' => 'These Services do not address anyone under the age of 13.',
        'Changes to Privacy Policy' => 'We may update our Privacy Policy from time to time.',
        'Contact Us' => 'If you have any questions or suggestions about our Privacy Policy, do not hesitate to contact us.'
    ];
    
    foreach ($privacy_sections as $title => $content) {
        if (stripos($title, $search_query) !== false || stripos($content, $search_query) !== false) {
            $search_results[] = [
                'title' => $title,
                'content' => $content,
                'relevance' => (stripos($title, $search_query) !== false) ? 'high' : 'medium'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy and Policy</title>
    <link rel="stylesheet" href="pravicy.css">
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
        <p>Try searching for terms like "information", "cookies", "data", or "security"</p>
    </section>
    <?php endif; ?>

    <main>
        <h6><a href="help center.php">Help home ></a><a href="pravicy.php">Privacy and Policy</a></h6>
        <h1>Privacy and Policy</h1>
        <section class="instructions">
      <p>
        Built the Find hotel app as a Commercial app. This SERVICE is provided by and is intended for use as is.
      </p>
      <p>
        This page is used to inform visitors regarding our policies with the collection, use, and disclosure of Personal Information if anyone decided to use our Service.
      </p>
      <p>
        If you choose to use our Service, then you agree to the collection and use of information in relation to this policy. The Personal Information that we collect is used for providing and improving the Service. We will not use or share your information with anyone except as described in this Privacy Policy.
      </p>
      <p>
        The terms used in this Privacy Policy have the same meanings as in our Terms and Conditions, which is accessible at Find hotel unless otherwise defined in this Privacy Policy.
      </p>

      <h3>Information Collection and Use</h3>
      <p>
        For a better experience, while using our Service, we may require you to provide us with certain personally identifiable information. The information that we request will be retained by us and used as described in this privacy policy.
      </p>
      <p>
        The app does use third-party services that may collect information used to identify you.
      </p>
      <p>
        Link to privacy policy of third-party service providers used by the app.
      </p>

      <h3>Log Data</h3>
      <p>
        We want to inform you that whenever you use our Service, in a case of an error in the app we collect data and information (through third-party products) on your phone called Log Data. This Log Data may include information such as your device Internet Protocol ("IP") address, device name, operating system version, the configuration of the app when utilizing our Service, the time and date of your use of the Service, and other statistics.
      </p>

      <h3>Cookies</h3>
      <p>
        Cookies are files with a small amount of data that are commonly used as anonymous unique identifiers.
      </p>

      <h3>Service Providers</h3>
      <p>
        We may employ third-party companies and individuals due to the following reasons:
      </p>
      <ul>
        <li>To facilitate our Service;</li>
        <li>To provide the Service on our behalf;</li>
        <li>To perform Service-related services; or</li>
        <li>To assist us in analyzing how our Service is used.</li>
      </ul>

      <h3>Security</h3>
      <p>
        We value your trust in providing us your Personal Information, thus we are striving to use commercially acceptable means of protecting it. But remember that no method of transmission over the internet, or method of electronic storage is 100% secure and reliable, and we cannot guarantee its absolute security.
      </p>

      <h3>Children's Privacy</h3>
      <p>
        These Services do not address anyone under the age of 13. We do not knowingly collect personally identifiable information from children under 13. In the case we discover that a child under 13 has provided us with personal information, we immediately delete this from our servers.
      </p>

      <h3>Changes to This Privacy Policy</h3>
      <p>
        We may update our Privacy Policy from time to time. Thus, you are advised to review this page periodically for any changes. We will notify you of any changes by posting the new Privacy Policy on this page.
      </p>
      <p>
        This policy is effective as of <?php echo date('Y-m-d'); ?>
      </p>

      <h3>Contact Us</h3>
      <p>
        If you have any questions or suggestions about our Privacy Policy, do not hesitate to contact us at:
      </p>
      <ul>
        <li>Email: privacy@petshop.com</li>
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
