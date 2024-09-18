<?php
session_start();

// Redirect to the main page if the user is already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="login-form-container">
        <h2>Login</h2>
        <form method="POST" action="include/authenticate.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn-submit">Login</button>
        </form>
        <?php
        // Display error message if login fails
        if (isset($_SESSION['login_error'])) {
            echo '<div class="error">' . $_SESSION['login_error'] . '</div>';
            unset($_SESSION['login_error']); // Clear the error message
        }
        ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>
