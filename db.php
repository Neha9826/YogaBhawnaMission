<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db = "yog_bhawna_misson";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
