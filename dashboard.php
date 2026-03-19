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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_organizer_code']) && $role === 'user') {
    $new_code = trim($_POST['new_organizer_code']);
    
    // Check if code exists
    $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE role = 'admin' AND organizer_code = ?");
    $stmt->execute([$new_code]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        $new_linked_id = $admin['user_id'];
        
        // Update user
        $updateStmt = $pdo->prepare("UPDATE Users SET linked_organizer_id = ? WHERE user_id = ?");
        if ($updateStmt->execute([$new_linked_id, $user_id])) {
            $_SESSION['linked_organizer_id'] = $new_linked_id;
            $success = "Successfully joined the new organizer's events! You can now browse their events.";
        } else {
            $error = "Failed to update your organizer. Please try again.";
        }
    } else {
        $error = "Invalid Organizer Access Code. Please try again.";
    }
}

$organizer_code = null;
if ($role === 'admin') {
    $stmt = $pdo->prepare("SELECT organizer_code FROM Users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $organizer_code = $stmt->fetchColumn();
}

require_once 'includes/header.php'; 
?>
<div class="card" style="margin-top: 2rem; max-width: 800px; margin-left: auto; margin-right: auto; text-align: center;">
    <h2>Welcome to your Dashboard, <span class="text-accent"><?= htmlspecialchars($name) ?></span>!</h2>
    <p class="text-muted" style="font-size: 1.1rem;">You are logged in as a <strong class="badge badge-neutral" style="margin-left: 5px;"><?= ucfirst(htmlspecialchars($role)) ?></strong></p>
    
    <?php if (!empty($error)): ?> 
        <div class="alert alert-error" style="margin-top: 15px; text-align: left;">
            <span style="font-size: 1.2rem; margin-right: 8px;">⚠️</span> <?= htmlspecialchars($error) ?>
        </div> 
    <?php endif; ?>
    <?php if (!empty($success)): ?> 
        <div class="alert alert-success" style="margin-top: 15px; text-align: left;">
            <span style="font-size: 1.2rem; margin-right: 8px;">✨</span> <?= htmlspecialchars($success) ?>
        </div> 
    <?php endif; ?>
    <?php if ($role === 'admin' && $organizer_code): ?>
        <div style="margin-top: 25px; padding: 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 12px; display: inline-block;">
            <span class="text-muted" style="margin-right: 10px;">Your Organizer Code:</span>
            <strong style="color: var(--secondary); font-size: 1.3rem; letter-spacing: 2px; font-family: monospace;"><?= htmlspecialchars($organizer_code) ?></strong>
            <p style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted);">Share this code with your target users so they can register and access your events.</p>
        </div>
    <?php endif; ?>
    <div style="margin-top: 40px; display: flex; justify-content: center; flex-wrap: wrap; gap: 20px;">
        <?php if ($role === 'admin'): ?>
            <a href="create_event.php" class="btn btn-primary" style="width: auto;"><span>➕</span> Create New Event</a>
            <a href="checkin.php" class="btn btn-secondary" style="width: auto;"><span>📷</span> Scanner</a>
            <a href="my_events.php" class="btn btn-outline" style="width: auto;"><span>📊</span> Manage My Events</a>
        <?php else: ?>
            <a href="index.php" class="btn btn-primary" style="width: auto;"><span>🎟️</span> Browse Events</a>
            <a href="my_tickets.php" class="btn btn-outline" style="width: auto;"><span>📱</span> View My Tickets</a>
            
            <div style="width: 100%; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                <h3 style="margin-bottom: 15px; font-size: 1.2rem;">Join Another Organizer</h3>
                <form method="POST" action="dashboard.php" style="display: flex; gap: 10px; justify-content: center; max-width: 500px; margin: 0 auto;">
                    <input type="text" name="new_organizer_code" placeholder="Enter Organizer Code (e.g. ORG-XXXXXX)" required style="flex: 1; min-width: 0;">
                    <button type="submit" class="btn btn-secondary" style="width: auto; white-space: nowrap;">Join Events</button>
                </form>
                <p style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted);">Enter an organizer code to join their events. This will replace your current default organizer in Browse Events.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>