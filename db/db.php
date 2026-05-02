<?php
// Database credentials
$host = 'localhost';
$db   = '001_wedding_schema';
$user = 'root';
$pass = ''; // Leave empty if using XAMPP default
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options for safety and error reporting
$options = [
    PDO::ATTR_ERR_MODE            => PDO::ERR_MODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES    => false,
];

try {
     // Create the connection
     $pdo = new PDO($dsn, $user, $pass, $options);
     // If you see nothing on the screen, it worked!
} catch (\PDOException $e) {
     // If connection fails, show the error message
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>