<?php
require_once 'config/config.php';

// Destroy session and redirect
session_destroy();
setMessage('You have been logged out successfully.', 'success');
redirect('index.php');
?>
