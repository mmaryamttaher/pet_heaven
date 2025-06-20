<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setMessage('Please log in to manage your pets.', 'error');
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user's pets
$pets_query = "SELECT * FROM pets WHERE user_id = ? ORDER BY created_at DESC";
$pets_stmt = $db->prepare($pets_query);
$pets_stmt->execute([$_SESSION['user_id']]);
$pets = $pets_stmt->fetchAll();

// Handle form submission for adding/updating pet
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_name = sanitizeInput($_POST['pet_name']);
    $age = sanitizeInput($_POST['age']);
    $type = sanitizeInput($_POST['type']);
    $weight = sanitizeInput($_POST['weight']);
    $pet_id = $_POST['pet_id'] ?? null;
    
    if (empty($pet_name) || empty($type)) {
        setMessage('Pet name and type are required.', 'error');
    } else {
        if ($pet_id) {
            // Update existing pet
            $update_query = "UPDATE pets SET name = ?, age = ?, type = ?, weight = ? WHERE id = ? AND user_id = ?";
            $update_stmt = $db->prepare($update_query);
            
            if ($update_stmt->execute([$pet_name, $age, $type, $weight, $pet_id, $_SESSION['user_id']])) {
                setMessage('Pet information updated successfully!', 'success');
            } else {
                setMessage('Error updating pet information.', 'error');
            }
        } else {
            // Add new pet
            $insert_query = "INSERT INTO pets (user_id, name, age, type, weight) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            
            if ($insert_stmt->execute([$_SESSION['user_id'], $pet_name, $age, $type, $weight])) {
                setMessage('Pet added successfully!', 'success');
            } else {
                setMessage('Error adding pet.', 'error');
            }
        }
        redirect('pet.php');
    }
}

// Handle pet deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pet_id = $_GET['delete'];
    $delete_query = "DELETE FROM pets WHERE id = ? AND user_id = ?";
    $delete_stmt = $db->prepare($delete_query);
    
    if ($delete_stmt->execute([$pet_id, $_SESSION['user_id']])) {
        setMessage('Pet deleted successfully!', 'success');
    } else {
        setMessage('Error deleting pet.', 'error');
    }
    redirect('pet.php');
}

// Get pet for editing if edit parameter is provided
$editing_pet = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $pet_id = $_GET['edit'];
    $edit_query = "SELECT * FROM pets WHERE id = ? AND user_id = ?";
    $edit_stmt = $db->prepare($edit_query);
    $edit_stmt->execute([$pet_id, $_SESSION['user_id']]);
    $editing_pet = $edit_stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PET INFORMATION</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="pet.css">
</head>
<body>
       <!--start header-->
       <header class="navbar navbar-expand-lg  " style="position: fixed; " >
        <div class="container">
            <img src="imgs/Preview-removebg-preview.png" alt="logo" height="100" width="150">
            <a href="#menue" class="navbar-toggler collapsed"  data-bs-toggle=collapse data-bs-target="#menue"  aria-expanded="false" >
                <span class="navbar-toggler-icon "></span>
            </a>
            <nav class="collapse  navbar-collapse justify-content-end " id="menue"  >
                <ul >
                    <li class="navbar-item">
                        <a href="index.php" class="nav-link active">HOME</a>
                    </li>
                    <li class="navbar-item">
                        <a href="search.php" class="nav-link ">Search</a>
                    </li>
                    <li class="navbar-item">
                        <a href="booking.php" class="nav-link ">Booking</a>
                    </li>
                    <li class="navbar-item">
                        <a href="#contact" class="nav-link">Contact Us</a>
                    </li>
                    <li class="navbar-item">
                        <a href="user.php" class="nav-link"><i class="fa-regular fa-user"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
<!--end header-->

<?php
// Display messages
$message = getMessage();
if ($message):
?>
<div class="alert alert-<?php echo $message['type'] == 'error' ? 'danger' : $message['type']; ?> alert-dismissible fade show" role="alert" style="margin-top: 120px;">
    <?php echo $message['message']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!--start info-->
<section id="personalinfo">
    <div id="pic">
        <img src="imgs/ec5a095a2c354f3d981bacad02885f33.jpg"  alt="profile" style="border-radius: 60px;" width="70px" height="70px">
    </div>
    
    <form method="POST" id="petForm">
        <?php if ($editing_pet): ?>
            <input type="hidden" name="pet_id" value="<?php echo $editing_pet['id']; ?>">
            <h3 style="text-align: center; margin-bottom: 20px;">Edit Pet Information</h3>
        <?php else: ?>
            <h3 style="text-align: center; margin-bottom: 20px;">Add New Pet</h3>
        <?php endif; ?>
        
        <div id="info">
            <label>PET Name</label>
            <div class="combo d-flex" >
                <i class="fa-solid fa-shield-dog" style="color: #491503; padding-right: 10px;"></i>
                <div class="row" >
                    <div class="col">
                      <input type="text" name="pet_name" class="form-control" placeholder="Pet name" aria-label="Pet name" style="border: none;" value="<?php echo $editing_pet ? htmlspecialchars($editing_pet['name']) : ''; ?>" required>
                    </div>
                  </div>
            </div> <hr>
            
            <label for="age" class="form-label">Age</label>
            <div class="combo d-flex">
                <i class="fa-solid fa-0" style="color: #491503; padding-right: 10px;"></i>
                <div class="mb-3">
                    <input type="number" name="age" class="form-control" id="age" placeholder="Pet age" style="border: none;" value="<?php echo $editing_pet ? $editing_pet['age'] : ''; ?>" min="0" max="30">
                  </div>
            </div> <hr>
            
            <label>Type</label>
            <div class="combo">
                <i class="fa-solid fa-paw" style="color: #491503;"></i>
                    <select name="type" class="form-select" aria-label="Pet type" style="width: 15%; display: inline;" required>
                        <option value="">Your Pet</option>
                        <option value="dog" <?php echo ($editing_pet && $editing_pet['type'] == 'dog') ? 'selected' : ''; ?>>Dog</option>
                        <option value="cat" <?php echo ($editing_pet && $editing_pet['type'] == 'cat') ? 'selected' : ''; ?>>Cat</option>
                        <option value="turtle" <?php echo ($editing_pet && $editing_pet['type'] == 'turtle') ? 'selected' : ''; ?>>Turtle</option>
                        <option value="bird" <?php echo ($editing_pet && $editing_pet['type'] == 'bird') ? 'selected' : ''; ?>>Bird</option>
                    </select>
            </div> <hr>
            
            <label for="weight" class="form-label">Weight</label>
            <div class="combo d-flex">
                <i class="fa-solid fa-weight-scale" style="color: #491503; padding-right: 10px;"></i>
                <div class="mb-3">
                    <input type="number" name="weight" class="form-control" id="weight" placeholder="Pet Weight (kg)" style="border: none;" value="<?php echo $editing_pet ? $editing_pet['weight'] : ''; ?>" step="0.1" min="0">
                  </div>
            </div> <hr>
        </div>
        
        <div id="buttons">
            <div >
                <button type="submit" class="btn btn-light"><?php echo $editing_pet ? 'Update' : 'Save'; ?></button>
            </div>
            <div >
                <button type="reset" class="btn btn-light">Reset</button>
            </div>
            <?php if ($editing_pet): ?>
            <div>
                <a href="pet.php" class="btn btn-secondary">Cancel</a>
            </div>
            <?php endif; ?>
        </div>
    </form>
</section>
<!--end info-->

<!-- start pets list -->
<?php if (!empty($pets)): ?>
<section id="petslist" style="margin: 40px auto; max-width: 800px; padding: 20px;">
    <h3 style="text-align: center; margin-bottom: 30px;">My Pets</h3>
    <div class="row">
        <?php foreach ($pets as $pet): ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fa-solid fa-paw" style="color: #491503; margin-right: 10px;"></i>
                        <?php echo htmlspecialchars($pet['name']); ?>
                    </h5>
                    <p class="card-text">
                        <strong>Type:</strong> <?php echo ucfirst($pet['type']); ?><br>
                        <?php if ($pet['age']): ?>
                            <strong>Age:</strong> <?php echo $pet['age']; ?> years<br>
                        <?php endif; ?>
                        <?php if ($pet['weight']): ?>
                            <strong>Weight:</strong> <?php echo $pet['weight']; ?> kg<br>
                        <?php endif; ?>
                        <?php if ($pet['breed']): ?>
                            <strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); ?><br>
                        <?php endif; ?>
                    </p>
                    <div class="d-flex gap-2">
                        <a href="pet.php?edit=<?php echo $pet['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-edit"></i> Edit
                        </a>
                        <a href="pet.php?delete=<?php echo $pet['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this pet?')">
                            <i class="fa-solid fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php else: ?>
<section id="nopets" style="margin: 40px auto; max-width: 600px; padding: 20px; text-align: center;">
    <i class="fa-solid fa-paw fa-3x" style="color: #ccc; margin-bottom: 20px;"></i>
    <h4>No pets added yet</h4>
    <p class="text-muted">Add your first pet using the form above</p>
</section>
<?php endif; ?>
<!-- end pets list -->

<!--start footer-->
<footer id="contact">
    <img src="imgs/Preview-removebg-preview.png" alt="logo" height="100" width="150">
    <div class="icons">
        <a href=""><i class="fa-brands fa-facebook" style="color: #2958a8;"></i></a>
        <a href=""><i class="fa-brands fa-instagram" style="color: #e713a4;"></i></a>
        <a href=""><i class="fa-brands fa-whatsapp" style="color: #398f00;"></i></a>
        <a href="chat.php"><i class="fa-regular fa-comments"></i></a>
    </div>
</footer>
<!--end footer-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
