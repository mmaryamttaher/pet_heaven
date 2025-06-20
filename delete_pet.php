<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setMessage('Please log in to delete pet information.', 'error');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pet_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $pet_id = intval($_POST['pet_id']);
    
    // Verify pet ownership
    $query = "SELECT name FROM pets WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$pet_id, $_SESSION['user_id']]);
    $pet = $stmt->fetch();
    
    if (!$pet) {
        setMessage('Pet not found or you do not have permission to delete this pet.', 'error');
        redirect('user.php');
    }
    
    // Check if pet has any bookings
    $query = "SELECT COUNT(*) as booking_count FROM bookings WHERE pet_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$pet_id]);
    $result = $stmt->fetch();
    
    if ($result['booking_count'] > 0) {
        setMessage('Cannot delete pet with existing bookings. Please contact support if you need to remove this pet.', 'error');
        redirect('user.php');
    }
    
    // Delete the pet
    $query = "DELETE FROM pets WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$pet_id, $_SESSION['user_id']])) {
        setMessage('Pet "' . htmlspecialchars($pet['name']) . '" has been deleted successfully.', 'success');
    } else {
        setMessage('Error deleting pet. Please try again.', 'error');
    }
} else {
    setMessage('Invalid request.', 'error');
}

redirect('user.php');
?>
