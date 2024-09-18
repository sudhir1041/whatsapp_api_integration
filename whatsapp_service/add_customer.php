<?php
session_start();
include_once 'include/database.php'; // Assuming this file manages database connections

// Redirect to the login page if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle manual customer addition
if (isset($_POST['add_customer'])) {
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $email = htmlspecialchars($_POST['email']);

    if (!empty($name) && !empty($phone) && !empty($email)) {
        // Check if the customer already exists by phone or email
        $stmt = $conn->prepare("SELECT * FROM wp_customers WHERE Phone = ? OR Email = ?");
        $stmt->bind_param("ss", $phone, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = '<div class="notification notification-error">Customer with this phone number or email already exists.</div>';
        } else {
            // If customer does not exist, insert the new customer
            $stmt = $conn->prepare("INSERT INTO wp_customers (Name, Phone, Email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $phone, $email);
            if ($stmt->execute()) {
                $message = '<div class="notification notification-success">Customer added successfully!</div>';
            } else {
                $message = '<div class="notification notification-error">Error adding customer.</div>';
            }
        }
        $stmt->close();
    } else {
        $message = '<div class="notification notification-error">Please fill in all fields.</div>';
    }
}

// Handle CSV import
if (isset($_POST['import'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['csv_file']['tmp_name'];
        $fileType = $_FILES['csv_file']['type'];
        
        if ($fileType === 'text/csv' || $fileType === 'application/vnd.ms-excel') {
            if (($handle = fopen($fileTmpPath, 'r')) !== FALSE) {
                // Skip the header row
                fgetcsv($handle);
                
                $stmt = $conn->prepare("INSERT INTO wp_customers (Name, Phone, Email) VALUES (?, ?, ?)");

                while (($data = fgetcsv($handle)) !== FALSE) {
                    $name = htmlspecialchars($data[0]);
                    $phone = htmlspecialchars($data[1]);
                    $email = htmlspecialchars($data[2]);

                    // Check if the customer already exists
                    $check_stmt = $conn->prepare("SELECT * FROM wp_customers WHERE Phone = ? OR Email = ?");
                    $check_stmt->bind_param("ss", $phone, $email);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();

                    if ($check_result->num_rows == 0) {
                        // If customer doesn't exist, insert into database
                        $stmt->bind_param("sss", $name, $phone, $email);
                        $stmt->execute();
                    }
                }

                fclose($handle);
                $stmt->close();
                $message = '<div class="notification notification-success">Data imported successfully!</div>';
            } else {
                $message = '<div class="notification notification-error">Error opening the file.</div>';
            }
        } else {
            $message = '<div class="notification notification-error">Please upload a valid CSV file.</div>';
        }
    } else {
        $message = '<div class="notification notification-error">No file uploaded or upload error.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your CSS file -->
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="main-container">
        <section class="customer-management">
            <h1 class="page-title">Manage Customers</h1>

            <!-- Display messages -->
            <?php if (isset($message)) echo $message; ?>

            <!-- Manual customer addition form -->
            <div class="form-container form-add-customer">
                <h2 class="form-title">Add Customer</h2>
                <form action="" method="post" class="form">
                    <div class="contact-details">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>
                    <div class="contact-details">
                        <label for="phone" class="form-label">Phone:</label>
                        <input type="text" id="phone" name="phone" class="form-input" required>
                    </div>
                    <div class="contact-details">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    
                    <button type="submit" name="add_customer" class="btn-submit">Add Customer</button>
                </form>
            </div>

            <!-- CSV file import form -->
            <div class="form-container form-import-csv">
                <h2 class="form-title">Import Customers from CSV</h2>
                <form action="" method="post" enctype="multipart/form-data" class="form">
                    <label for="csv_file" class="form-label">Choose CSV file:</label>
                    <input type="file" id="csv_file" name="csv_file" class="form-input" accept=".csv" required>
                    <button type="submit" name="import" class="btn-submit">Import</button>
                </form>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
