<?php
// Simple setup script to initialize the database
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Heaven - Setup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Pet Heaven - Database Setup</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        if (isset($_POST['setup'])) {
                            try {
                                require_once 'config/database.php';
                                
                                // First, connect without specifying database to create it
                                $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                
                                // Read and execute SQL file
                                $sql = file_get_contents('database/schema.sql');
                                
                                // Split SQL into individual statements
                                $statements = array_filter(array_map('trim', explode(';', $sql)));
                                
                                foreach ($statements as $statement) {
                                    if (!empty($statement)) {
                                        $pdo->exec($statement);
                                    }
                                }
                                
                                echo '<div class="alert alert-success">';
                                echo '<h4>Database Setup Complete!</h4>';
                                echo '<p>Your Pet Heaven database has been successfully created with sample data.</p>';
                                echo '<h5>Default Admin Credentials:</h5>';
                                echo '<ul>';
                                echo '<li><strong>Email:</strong> admin@petheaven.com</li>';
                                echo '<li><strong>Password:</strong> password</li>';
                                echo '</ul>';
                                echo '<p><a href="index.php" class="btn btn-primary">Go to Website</a> ';
                                echo '<a href="login.php" class="btn btn-success">Admin Login</a></p>';
                                echo '</div>';
                                
                            } catch (PDOException $e) {
                                echo '<div class="alert alert-danger">';
                                echo '<h4>Setup Error</h4>';
                                echo '<p>Error setting up database: ' . $e->getMessage() . '</p>';
                                echo '</div>';
                            }
                        } else {
                            ?>
                            <h4>Welcome to Pet Heaven Setup</h4>
                            <p>This will create the database and populate it with sample data.</p>
                            
                            <div class="alert alert-info">
                                <h5>Before proceeding, make sure:</h5>
                                <ul>
                                    <li>XAMPP is running (Apache and MySQL)</li>
                                    <li>Your database configuration in <code>config/database.php</code> is correct</li>
                                    <li>MySQL is accessible with the configured credentials</li>
                                </ul>
                            </div>
                            
                            <form method="POST">
                                <button type="submit" name="setup" class="btn btn-primary btn-lg">Setup Database</button>
                            </form>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
