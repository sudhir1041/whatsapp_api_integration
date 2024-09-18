<?php
session_start();
include_once 'include/database.php'; // Assuming this file manages database connections

// Redirect to the login page if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pagination logic
$limit = 10; // Number of messages per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total number of records
$total_query = "SELECT COUNT(*) AS total_messages FROM wp_sent_messages";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_messages = $total_row['total_messages'];

// Fetch the latest 10 messages with pagination
$query = "SELECT phone_number, customer_name, message, sent_at FROM wp_sent_messages ORDER BY sent_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Calculate total pages
$total_pages = ceil($total_messages / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sent Messages</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="main-container">
        <section class="message-section">
            <h1 class="message-heading">Sent Messages</h1>
            
            
            <?php if ($result->num_rows > 0): ?>
                <table class="message-table">
                    <thead>
                        <tr>
                            <th>Phone Number</th>
                            <th>Customer Name</th>
                            <th>Message Content</th>
                            <th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>+<?php echo htmlspecialchars($row['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['message']); ?></td>
                                <td><?php echo htmlspecialchars($row['sent_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Pagination Buttons -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="sent_messages.php?page=<?php echo $page - 1; ?>" class="pagination-btn">Previous</a>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="sent_messages.php?page=<?php echo $page + 1; ?>" class="pagination-btn">Next</a>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <p class="no-messages">No messages have been sent yet.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'footer.php'; ?> 
</body>
</html>
