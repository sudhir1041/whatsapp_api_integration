<?php
session_start();
include_once 'database.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch the user from the database (replace with actual user data)
    $query = "SELECT * FROM wp_users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Correct login
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];

            // Redirect to the main page
            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['login_error'] = 'Invalid password. Please try again.';
        }
    } else {
        $_SESSION['login_error'] = 'Invalid username. Please try again.';
    }

    // Redirect back to the login page if failed
    header('Location: ../login.php');
    exit;
}
?>
