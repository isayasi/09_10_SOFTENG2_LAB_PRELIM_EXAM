<?php
$host = 'localhost';
$dbname = 'school_newspaper';
$username = 'root';
$password = '';

define("BASE_URL", "http://localhost/school_newspaper/");
define("UPLOADS_URL", BASE_URL . "uploads/");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>