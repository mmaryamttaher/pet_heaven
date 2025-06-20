<?php
$page_title = "Edit Pet";
$css_file = "pet.css";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setMessage('Please log in to edit pet information.', 'error');
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get pet ID
$pet_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$pet_id) {
    setMessage('Invalid pet selected.', 'error');
    redirect('user.php');
}

// Get pet details and verify ownership
$query = "SELECT * FROM pets WHERE id = ? AND user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$pet_id, $_SESSION['user_id']]);
$pet = $stmt->fetch();

if (!$pet) {
    setMessage('Pet not found or you do not have permission to edit this pet.', 'error');
    redirect('user.php');
}

// Handle pet update
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
        $query = "UPDATE pets SET name = ?, type = ?, breed = ?, age = ?, weight = ?, special_needs = ?, vaccination_status = ? WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$name, $type, $breed, $age, $weight, $special_needs, $vaccination_status, $pet_id, $_SESSION['user_id']])) {
            setMessage('Pet information updated successfully!', 'success');
            redirect('user.php');
        } else {
            setMessage('Error updating pet information. Please try again.', 'error');
        }
    }
}
?>

<div class="container" style="margin-top: 150px;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Pet Information</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Pet Name *</label>
                                    <input type="text" class="form-control" name="name" required value="<?php echo htmlspecialchars($pet['name']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Pet Type *</label>
                                    <select class="form-select" name="type" required>
                                        <option value="">Select pet type</option>
                                        <option value="dog" <?php echo $pet['type'] == 'dog' ? 'selected' : ''; ?>>Dog</option>
                                        <option value="cat" <?php echo $pet['type'] == 'cat' ? 'selected' : ''; ?>>Cat</option>
                                        <option value="bird" <?php echo $pet['type'] == 'bird' ? 'selected' : ''; ?>>Bird</option>
                                        <option value="other" <?php echo $pet['type'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="breed" class="form-label">Breed</label>
                                    <input type="text" class="form-control" name="breed" value="<?php echo htmlspecialchars($pet['breed'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="age" class="form-label">Age (years)</label>
                                    <input type="number" class="form-control" name="age" min="0" max="30" value="<?php echo $pet['age']; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Weight (kg)</label>
                                    <input type="number" step="0.1" class="form-control" name="weight" min="0" value="<?php echo $pet['weight']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vaccination_status" class="form-label">Vaccination Status</label>
                                    <select class="form-select" name="vaccination_status">
                                        <option value="none" <?php echo $pet['vaccination_status'] == 'none' ? 'selected' : ''; ?>>None</option>
                                        <option value="partial" <?php echo $pet['vaccination_status'] == 'partial' ? 'selected' : ''; ?>>Partial</option>
                                        <option value="up_to_date" <?php echo $pet['vaccination_status'] == 'up_to_date' ? 'selected' : ''; ?>>Up to Date</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_needs" class="form-label">Special Needs or Medical Conditions</label>
                            <textarea class="form-control" name="special_needs" rows="3" placeholder="Any special care requirements, medical conditions, or behavioral notes..."><?php echo htmlspecialchars($pet['special_needs'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Pet</button>
                            <a href="user.php" class="btn btn-secondary">Cancel</a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deletePetModal">Delete Pet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Pet Modal -->
<div class="modal fade" id="deletePetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Pet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($pet['name']); ?></strong>?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone. All booking history for this pet will also be affected.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="delete_pet.php" style="display: inline;">
                    <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                    <button type="submit" class="btn btn-danger">Delete Pet</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
