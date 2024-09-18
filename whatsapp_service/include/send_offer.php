<?php
session_start();
include_once 'database.php'; // Assuming this file manages database connections
include_once 'whatsapp_api.php'; // Assuming this file contains the send_message_to_customer function

// Redirect to the login page if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize an array to store messages if it hasn't been set already
if (!isset($_SESSION['offer_messages'])) {
    $_SESSION['offer_messages'] = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify nonce for CSRF protection
    if (!isset($_POST['send_message_nonce']) || !hash_equals($_SESSION['send_message_nonce'], $_POST['send_message_nonce'])) {
        $_SESSION['offer_messages'][] = '<div class="error">Invalid request. Please try again.</div>';
        header('Location: ../index.php');
        exit;
    }

    // Sanitize and validate input
    $custom_message = htmlspecialchars($_POST['custom_message']);
    $selected_customers_data = json_decode($_POST['selected_customers_data'], true); // Decoding the JSON array of customers

    if (empty($selected_customers_data)) {
        $_SESSION['offer_messages'][] = '<div class="error">No customers selected.</div>';
        header('Location: ../index.php');
        exit;
    }

    // Flag to check if all messages are sent successfully
    $all_messages_sent = true;

    // Loop through each selected customer and send the custom message
    foreach ($selected_customers_data as $selected_customer) {
        // Split the selected customer string into phone number and name (assuming format 'phone_number|customer_name')
        list($phone_number, $customer_name) = explode('|', htmlspecialchars($selected_customer));

        // Send the custom message to the customer via WhatsApp API
        $response = send_offer_to_customer($phone_number, $customer_name, $custom_message);

        // Check the response and add the result to the session messages
        if ($response['error']) {
            $_SESSION['offer_messages'][] = '<div class="error">' . htmlspecialchars($response['message']) . '</div>';
            $all_messages_sent = false;
        } else {
            $_SESSION['offer_messages'][] = '<div class="success">Message sent successfully to ' . htmlspecialchars($customer_name) . '!</div>';

            // Insert the message into the 'message_offer' table
            $stmt = $conn->prepare("INSERT INTO wp_sent_messages (phone_number, customer_name, message, sent_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $phone_number, $customer_name, $custom_message);
            
            if (!$stmt->execute()) {
                $_SESSION['offer_messages'][] = '<div class="error">Failed to store message in the database for ' . htmlspecialchars($customer_name) . '.</div>';
                $all_messages_sent = false;
            }
        }
    }

    // If not all messages were sent successfully, show a general error message
    if (!$all_messages_sent) {
        $_SESSION['offer_messages'][] = '<div class="error">Some messages could not be sent. Please try again or check the errors above.</div>';
    }

    // Redirect back to the form page after processing all customers
    header('Location: ../index.php');
    exit;
} else {
    // If the request is not POST, redirect to the form page
    header('Location: ../index.php');
    exit;
}
