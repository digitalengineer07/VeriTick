<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$name = $_SESSION['name'];

require_once 'includes/header.php'; 
?>
<div class="card" style="margin-top: 2rem; max-width: 800px; margin-left: auto; margin-right: auto; text-align: center;">
    <h2>Welcome to your Dashboard, <span class="text-accent"><?= htmlspecialchars($name) ?></span>!</h2>
    <p class="text-muted" style="font-size: 1.1rem;">You are logged in as a <strong class="badge badge-neutral" style="margin-left: 5px;"><?= ucfirst(htmlspecialchars($role)) ?></strong></p>
    <div style="margin-top: 40px; display: flex; justify-content: center; flex-wrap: wrap; gap: 20px;">
        <?php if ($role === 'admin'): ?>
            <a href="create_event.php" class="btn btn-primary" style="width: auto;"><span>➕</span> Create New Event</a>
            <a href="checkin.php" class="btn btn-secondary" style="width: auto;"><span>📷</span> Scanner</a>
            <a href="my_events.php" class="btn btn-outline" style="width: auto;"><span>📊</span> Manage My Events</a>
        <?php else: ?>
            <a href="index.php" class="btn btn-primary" style="width: auto;"><span>🎟️</span> Browse Events</a>
            <a href="my_tickets.php" class="btn btn-outline" style="width: auto;"><span>📱</span> View My Tickets</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>