<?php
// include __DIR__ . "/config.php";
$host = "localhost";
$user = "root";
$pass = ""; 
$db = "yog_bhawna_misson";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
