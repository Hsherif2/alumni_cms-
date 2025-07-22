<?php
$host = '127.0.0.1';
$db   = 'alumni_cms';  // your database name
$user = 'root';        // default MAMP username
$pass = 'root';        // default MAMP password
$port = 8889;          // MAMP MySQL port

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
