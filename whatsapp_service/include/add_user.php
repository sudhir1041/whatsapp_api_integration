<?php
include_once 'database.php'; // Include your DB connection file

// Username and password
$username = ''; 
$plain_password = ''; 

// Hash the password using bcrypt
$hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

// Prepare SQL query to insert a new user into the wp_users table
$stmt = $conn->prepare("INSERT INTO wp_users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed_password);

if ($stmt->execute()) {
    echo "User successfully added!";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
