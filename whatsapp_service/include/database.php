<?php
// Database connection settings
$host = 'localhost'; // Your MySQL host
$username = 'wp_whatsapp'; // Your MySQL username
$password = ''; // Your MySQL password
$database = ''; // Your database name

// Create a new MySQLi connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>
