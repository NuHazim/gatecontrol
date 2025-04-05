<?php
$dsn = "mysql:host=localhost;dbname=bbbsek9;charset=utf8mb4";
$username = "root";
$password = "";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Enable exceptions for errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch as associative array
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
