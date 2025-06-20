<?php
require_once '../config/database.php';

try {
    // First, connect without specifying database to create it
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL file
    $sql = file_get_contents('schema.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "Database initialized successfully!<br>";
    echo "Default admin credentials:<br>";
    echo "Email: admin@petheaven.com<br>";
    echo "Password: password<br>";
    
} catch (PDOException $e) {
    echo "Error initializing database: " . $e->getMessage();
}
?>
