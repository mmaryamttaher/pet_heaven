<?php
$page_title = "Add Pet";
$css_file = "pet.css";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setMessage('Please log in to add a pet.', 'error');
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle pet addition
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $type = sanitizeInput($_POST['type']);
    $breed = sanitizeInput($_POST['breed']);
    $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
    $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : null;
    $special_needs = sanitizeInput($_POST['special_needs']);
    $vaccination_status = sanitizeInput($_POST['vaccination_status']);
    
    if (empty($name) || empty($type)) {
        setMessage('Pet name and type are required.', 'error');
    } else {
        $query = "INSERT INTO pets (user_id, name, type, breed, age, weight, special_needs, vaccination_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$_SESSION['user_id'], $name, $type, $breed, $age, $weight, $special_needs, $vaccination_status])) {
            setMessage('Pet added successfully!', 'success');
            redirect('user.php');
        } else {
            setMessage('Error adding pet. Please try again.', 'error');
        }
    }
}
?>

<div class="container" style="margin-top: 150px;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Add New Pet</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Pet Name *</label>
                                    <input type="text" class="form-control" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Pet Type *</label>
                                    <select class="form-select" name="type" required>
                                        <option value="">Select pet type</option>
                                        <option value="dog" <?php echo (isset($_POST['type']) && $_POST['type'] == 'dog') ? 'selected' : ''; ?>>Dog</option>
                                        <option value="cat" <?php echo (isset($_POST['type']) && $_POST['type'] == 'cat') ? 'selected' : ''; ?>>Cat</option>
                                        <option value="bird" <?php echo (isset($_POST['type']) && $_POST['type'] == 'bird') ? 'selected' : ''; ?>>Bird</option>
                                        <option value="other" <?php echo (isset($_POST['type']) && $_POST['type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="breed" class="form-label">Breed</label>
                                    <input type="text" class="form-control" name="breed" value="<?php echo isset($_POST['breed']) ? htmlspecialchars($_POST['breed']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="age" class="form-label">Age (years)</label>
                                    <input type="number" class="form-control" name="age" min="0" max="30" value="<?php echo isset($_POST['age']) ? $_POST['age'] : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Weight (kg)</label>
                                    <input type="number" step="0.1" class="form-control" name="weight" min="0" value="<?php echo isset($_POST['weight']) ? $_POST['weight'] : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vaccination_status" class="form-label">Vaccination Status</label>
                                    <select class="form-select" name="vaccination_status">
                                        <option value="none" <?php echo (isset($_POST['vaccination_status']) && $_POST['vaccination_status'] == 'none') ? 'selected' : ''; ?>>None</option>
                                        <option value="partial" <?php echo (isset($_POST['vaccination_status']) && $_POST['vaccination_status'] == 'partial') ? 'selected' : ''; ?>>Partial</option>
                                        <option value="up_to_date" <?php echo (isset($_POST['vaccination_status']) && $_POST['vaccination_status'] == 'up_to_date') ? 'selected' : ''; ?>>Up to Date</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_needs" class="form-label">Special Needs or Medical Conditions</label>
                            <textarea class="form-control" name="special_needs" rows="3" placeholder="Any special care requirements, medical conditions, or behavioral notes..."><?php echo isset($_POST['special_needs']) ? htmlspecialchars($_POST['special_needs']) : ''; ?></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Add Pet</button>
                            <a href="user.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
