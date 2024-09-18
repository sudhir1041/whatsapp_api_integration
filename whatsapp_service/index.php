<?php
session_start();
include_once 'include/database.php';

// Redirect to the login page if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Fetch customer phone numbers and names from the database
$query = "SELECT Phone, Name FROM wp_customers"; // Ensure the correct table name
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Whatsapp Message</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Basic styles for layout */
        .form-layout {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .left-side, .right-side {
            width: 48%;
        }
        .left-side ul, .right-side ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .left-side li, .right-side li {
            margin: 5px 0;
            cursor: pointer;
        }
        .left-side li.selected {
            background-color: #e0e0e0;
        }
        .right-side .remove-btn {
            margin-left: 10px;
            color: red;
            border: none;
            background: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="main-content">
        <div class="offer-form-container">
            <img src="https://cdn-icons-png.flaticon.com/512/5968/5968841.png" width="50px" height="50px" alt="whatsapp image">
            <h2>Send Whatsapp Message to Customers</h2>
            
            <!-- Display messages -->
            <?php
            if (isset($_SESSION['offer_messages'])) {
                foreach ($_SESSION['offer_messages'] as $message) {
                    echo $message;
                }
                unset($_SESSION['offer_messages']); // Clear messages after displaying
            }
            ?>

            <div class="form-layout">
                <!-- Left side: List of all customers to select -->
                <div class="left-side">
                    <h3>Available Customers:</h3>
                    <ul id="available_customers_list">
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $phone_number = htmlspecialchars($row['Phone']);
                                $customer_name = htmlspecialchars($row['Name']);
                                echo "<li data-value='{$phone_number}|{$customer_name}'>{$customer_name} ({$phone_number})</li>";
                            }
                        } else {
                            echo '<li>No customers found</li>';
                        }
                        ?>
                    </ul>
                </div>

                <!-- Right side: Custom message input and selected customers preview -->
                <div class="right-side">
                    <form id="message-form" method="POST" action="include/send_offer.php">
                        <div class="form-row">
                            <label for="custom_message">Custom Message:</label>
                            <textarea id="custom_message" name="custom_message" rows="5" placeholder="Enter your custom message" required></textarea>
                        </div>

                        <div id="selected-preview">
                            <h3>Selected Customers:</h3>
                            <ul id="selected_customers_list"></ul>
                        </div>

                        <input type="hidden" name="selected_customers_data" id="selected_customers_data">
                        <?php
                        // Generate a nonce and store it in the session
                        $_SESSION['send_message_nonce'] = bin2hex(random_bytes(32));
                        ?>
                        <input type="hidden" name="send_message_nonce" value="<?php echo htmlspecialchars($_SESSION['send_message_nonce']); ?>">
                        <button type="submit" class="btn-submit">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>

    <script>
        const availableCustomersList = document.getElementById('available_customers_list');
        const selectedCustomersList = document.getElementById('selected_customers_list');
        const selectedCustomersData = document.getElementById('selected_customers_data');
        const messageForm = document.getElementById('message-form');
        let selectedCustomers = [];

        // Event listener for when a customer is clicked in the available customers list
        availableCustomersList.addEventListener('click', function(event) {
            const target = event.target;
            if (target.tagName === 'LI' && !target.classList.contains('selected')) {
                // Create list item for selected customer with a remove button
                const listItem = document.createElement('li');
                listItem.innerHTML = target.textContent + ' <button class="remove-btn" data-value="' + target.getAttribute('data-value') + '">X</button>';
                selectedCustomersList.appendChild(listItem);

                // Add customer to selected customers array
                selectedCustomers.push(target.getAttribute('data-value'));
                selectedCustomersData.value = JSON.stringify(selectedCustomers);

                // Mark the customer as selected in the left-side list
                target.classList.add('selected');
            }
        });

        // Event listener for removing customers from the selected list
        selectedCustomersList.addEventListener('click', function(event) {
            if (event.target.tagName === 'BUTTON' && event.target.classList.contains('remove-btn')) {
                const dataValue = event.target.getAttribute('data-value');

                // Remove the list item
                event.target.parentElement.remove();

                // Remove customer from the selected customers array
                selectedCustomers = selectedCustomers.filter(customer => customer !== dataValue);
                selectedCustomersData.value = JSON.stringify(selectedCustomers);

                // Find and unselect the customer in the left-side list
                const availableItems = availableCustomersList.querySelectorAll('li');
                availableItems.forEach(function(item) {
                    if (item.getAttribute('data-value') === dataValue) {
                        item.classList.remove('selected');
                    }
                });
            }
        });

        // Form submission validation
        messageForm.addEventListener('submit', function(event) {
            if (selectedCustomers.length === 0) {
                alert('Please select at least one customer.');
                event.preventDefault(); // Prevent form submission
            }
        });

    </script>
</body>
</html>
