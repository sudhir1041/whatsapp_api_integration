<nav class="navbar">
    <div class="logo">
        <a href="index.php"><img src="https://nurserynisarga.in/wp-content/uploads/2019/03/blacklogo1.png" alt="Logo"></a> 
    </div>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <?php if (isset($_SESSION['username'])): ?>
            <li><a href="sent_messages.php">Sent Messages</a></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['username'])): ?>
            <li><a href="add_customer.php">Add Customer</a></li>
        <?php endif; ?>
        <li><a href="contact.php">Contact</a></li>
        <?php if (isset($_SESSION['username'])): ?>
            <li><a href="logout.php">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>
