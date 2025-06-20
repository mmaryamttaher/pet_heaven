<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setMessage('Please log in to manage your wallet.', 'error');
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user's payment cards
$cards_query = "SELECT * FROM payment_cards WHERE user_id = ? ORDER BY created_at DESC";
$cards_stmt = $db->prepare($cards_query);
$cards_stmt->execute([$_SESSION['user_id']]);
$cards = $cards_stmt->fetchAll();

// Handle form submission for adding card
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_number = sanitizeInput($_POST['card_number']);
    $card_holder = sanitizeInput($_POST['card_holder']);
    $expiry_date = sanitizeInput($_POST['expiry_date']);
    $cvv = sanitizeInput($_POST['cvv']);
    
    // Basic validation
    if (empty($card_number) || empty($card_holder) || empty($expiry_date) || empty($cvv)) {
        setMessage('All fields are required.', 'error');
    } elseif (strlen(str_replace(' ', '', $card_number)) < 16) {
        setMessage('Please enter a valid card number.', 'error');
    } elseif (strlen($cvv) < 3) {
        setMessage('Please enter a valid CVV.', 'error');
    } else {
        // Encrypt card number for security (in real app, use proper encryption)
        $masked_card = '**** **** **** ' . substr(str_replace(' ', '', $card_number), -4);
        
        // Insert card (store only masked version for security)
        $insert_query = "INSERT INTO payment_cards (user_id, card_holder_name, masked_card_number, expiry_date, card_type) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $db->prepare($insert_query);
        
        // Determine card type based on first digit
        $first_digit = substr(str_replace(' ', '', $card_number), 0, 1);
        $card_type = 'VISA'; // Default
        if ($first_digit == '4') $card_type = 'VISA';
        elseif ($first_digit == '5') $card_type = 'MASTERCARD';
        elseif ($first_digit == '3') $card_type = 'AMEX';
        
        if ($insert_stmt->execute([$_SESSION['user_id'], $card_holder, $masked_card, $expiry_date, $card_type])) {
            setMessage('Payment card added successfully!', 'success');
            redirect('wallet.php');
        } else {
            setMessage('Error adding payment card.', 'error');
        }
    }
}

// Handle card deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $card_id = $_GET['delete'];
    $delete_query = "DELETE FROM payment_cards WHERE id = ? AND user_id = ?";
    $delete_stmt = $db->prepare($delete_query);
    
    if ($delete_stmt->execute([$card_id, $_SESSION['user_id']])) {
        setMessage('Payment card deleted successfully!', 'success');
    } else {
        setMessage('Error deleting payment card.', 'error');
    }
    redirect('wallet.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visa Card</title>
    <link rel="stylesheet" href="wallet.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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

  <div class="window">
    <div class="card-container">
        <div class="card">
            <div class="chip"></div>
            <form method="POST" id="cardForm">
                <label for="card-number">Card Number</label>
                <input type="text" name="card_number" id="card-number" placeholder="0085 7789 2236 3685" maxlength="19" required>

                <label for="card-holder">Card Holder Name</label>
                <input type="text" name="card_holder" id="card-holder" placeholder="name" required>

                <div class="row">
                    <div class="col">
                        <label for="expiry-date">Expiry Date</label>
                        <input type="text" name="expiry_date" id="expiry-date" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div class="col">
                        <label for="cvv">CVV</label>
                        <input type="text" name="cvv" id="cvv" placeholder="321" maxlength="3" required>
                    </div>
                </div>
            </form>
            <div class="visa-logo">VISA</div>
        </div>
        <div class="column">
            <img src="imgs/f68618eff45eea357bb1cd1beecfc51d-removebg-preview.png" alt="" width="250" height="150">
            <br>
            <div class="btn">
                <a href="user.php"><button>Cancel</button></a>
                <button type="button" onclick="clearForm()">Clear</button>
                <button type="submit" form="cardForm">Add</button>
            </div>
        </div>
    </div>
  </div>

  <!-- Saved Cards Section -->
  <?php if (!empty($cards)): ?>
  <div class="saved-cards" style="margin: 40px auto; max-width: 800px; padding: 20px;">
      <h3 style="text-align: center; margin-bottom: 30px;">Saved Payment Cards</h3>
      <div class="row">
          <?php foreach ($cards as $card): ?>
          <div class="col-md-6 mb-4">
              <div class="card" style="border: 1px solid #ddd; border-radius: 10px; padding: 20px;">
                  <div class="card-body">
                      <h5 class="card-title">
                          <i class="fa-solid fa-credit-card" style="color: #491503; margin-right: 10px;"></i>
                          <?php echo htmlspecialchars($card['card_type']); ?>
                      </h5>
                      <p class="card-text">
                          <strong>Card Number:</strong> <?php echo htmlspecialchars($card['masked_card_number']); ?><br>
                          <strong>Card Holder:</strong> <?php echo htmlspecialchars($card['card_holder_name']); ?><br>
                          <strong>Expiry:</strong> <?php echo htmlspecialchars($card['expiry_date']); ?><br>
                          <small class="text-muted">Added: <?php echo date('M d, Y', strtotime($card['created_at'])); ?></small>
                      </p>
                      <div class="d-flex gap-2">
                          <a href="wallet.php?delete=<?php echo $card['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this card?')">
                              <i class="fa-solid fa-trash"></i> Delete
                          </a>
                      </div>
                  </div>
              </div>
          </div>
          <?php endforeach; ?>
      </div>
  </div>
  <?php endif; ?>

  <script>
    // Format card number with spaces
    document.getElementById('card-number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
    });

    // Format expiry date
    document.getElementById('expiry-date').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });

    // Only allow numbers for CVV
    document.getElementById('cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });

    // Clear form function
    function clearForm() {
        document.getElementById('cardForm').reset();
    }

    // Update card type logo based on card number
    document.getElementById('card-number').addEventListener('input', function(e) {
        const cardNumber = e.target.value.replace(/\s/g, '');
        const visaLogo = document.querySelector('.visa-logo');
        
        if (cardNumber.startsWith('4')) {
            visaLogo.textContent = 'VISA';
            visaLogo.style.color = '#1a1f71';
        } else if (cardNumber.startsWith('5')) {
            visaLogo.textContent = 'MASTERCARD';
            visaLogo.style.color = '#eb001b';
        } else if (cardNumber.startsWith('3')) {
            visaLogo.textContent = 'AMEX';
            visaLogo.style.color = '#006fcf';
        } else {
            visaLogo.textContent = 'VISA';
            visaLogo.style.color = '#1a1f71';
        }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
