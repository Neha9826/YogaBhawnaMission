<?php
// Database configuration
$host = "localhost";
$user = "root";
$password = ""; // your MySQL password
$dbname = "yog_bhawna_misson"; // your database name

// Create connection
$conn = mysqli_connect($host, $user, $password, $dbname);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
