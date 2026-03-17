<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VeriTick | Modern Ticketing</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div>
        <a href="index.php" style="text-decoration: none;">
            <h2><span class="logo-icon">✨</span> VeriTick</h2>
        </a>
    </div>
    <nav>
        <a href="index.php">Home</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="my_tickets.php">My Tickets</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="nav-highlight">Register</a>
        <?php endif; ?>
    </nav>
</header>
<div class="container">