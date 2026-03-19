<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$organizer_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT e.*, 
           (SELECT COUNT(*) FROM Tickets t WHERE t.event_id = e.event_id) AS tickets_sold,
           (SELECT COUNT(*) FROM Tickets t WHERE t.event_id = e.event_id AND t.used = 1) AS tickets_scanned
    FROM Events e 
    WHERE e.organizer_id = ? 
    ORDER BY e.date DESC
");
$stmt->execute([$organizer_id]);
$my_events = $stmt->fetchAll();

require_once 'includes/header.php'; 
?>
<div style="max-width: 1000px; margin: 2rem auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 15px;">
        <h2 style="margin: 0; font-size: 2rem;">My Organized Events</h2>
        <a href="create_event.php" class="btn btn-primary" style="width: auto; padding: 10px 20px;">+ New Event</a>
    </div>
    <div style="display: flex; flex-direction: column; gap: 25px;">
        <?php if (count($my_events) > 0): ?>
            <?php foreach ($my_events as $event): ?>
                <?php 
                    $sold_percent = ($event['total_seats'] > 0) ? round(($event['tickets_sold'] / $event['total_seats']) * 100) : 0;
                    $scanned_percent = ($event['tickets_sold'] > 0) ? round(($event['tickets_scanned'] / $event['tickets_sold']) * 100) : 0;
                    
                    $event_ts = strtotime($event['date']);
                    $now = time();
                    $is_past = ($event_ts + 86400) < $now; // Opacity dims only after it's Completed
                    
                    $status_html = '';
                    if ($event_ts > $now + 86400) {
                        $status_html = '<span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3);">Upcoming</span>';
                    } elseif ($event_ts > $now && $event_ts <= $now + 86400) {
                        $status_html = '<span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3);">Starting Soon</span>';
                    } elseif ($event_ts <= $now && $now <= $event_ts + 86400) {
                        $status_html = '<span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16,185,129,0.3);">Ongoing</span>';
                    } else {
                        $status_html = '<span class="badge badge-neutral">Completed</span>';
                    }
                ?>
                <div class="card" style="display: flex; flex-wrap: wrap; gap: 25px; align-items: center; <?= $is_past ? 'opacity: 0.6;' : '' ?>">
                    <div style="flex: 2; min-width: 250px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                            <h3 style="margin: 0; font-size: 1.6rem;"><?= htmlspecialchars($event['title']) ?></h3>
                            <?= $status_html ?>
                        </div>
                        <p style="margin-bottom: 5px; font-weight: 500; font-size: 0.95rem;"><span style="margin-right: 5px;">📅</span> <?= date('F j, Y, g:i a', strtotime($event['date'])) ?></p>
                        <p class="text-muted" style="font-weight: 500; font-size: 0.95rem;"><span style="margin-right: 5px;">📍</span> <?= htmlspecialchars($event['location']) ?></p>
                    </div>
                    <div style="flex: 2; min-width: 300px; background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95rem;">
                                <span style="font-weight: 600;">Tickets Claimed</span>
                                <strong><?= $event['tickets_sold'] ?> / <?= $event['total_seats'] ?> (<span style="color: var(--secondary);"><?= $sold_percent ?>%</span>)</strong>
                            </div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?= $sold_percent ?>%; background: var(--secondary);"></div>
                            </div>
                        </div>
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95rem;">
                                <span style="font-weight: 600;">Guests Checked In</span>
                                <strong><?= $event['tickets_scanned'] ?> / <?= $event['tickets_sold'] ?> (<span style="color: var(--primary);"><?= $scanned_percent ?>%</span>)</strong>
                            </div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?= $scanned_percent ?>%; background: var(--primary);"></div>
                            </div>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 150px; display: flex; flex-direction: column; gap: 12px;">
                        <a href="event_details.php?id=<?= $event['event_id'] ?>" class="btn btn-outline" style="padding: 12px;">View Page</a>
                        <a href="live_analytics.php?id=<?= $event['event_id'] ?>" class="btn btn-primary" style="padding: 12px; font-weight: 800; text-transform: uppercase;">Live Analytics</a>
                        <?php if (!$is_past): ?>
                            <a href="checkin.php" class="btn btn-secondary" style="padding: 12px;">Scanner</a>
                        <?php endif; ?>
                        <form action="delete_event.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this event? This action cannot be undone.');" style="margin: 0;">
                            <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                            <button type="submit" class="btn" style="padding: 12px; width: 100%; background: rgba(244, 63, 94, 0.1); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.3); font-weight: 800; text-transform: uppercase; cursor: pointer;">Delete Event</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card card-center" style="text-align: center;">
                <p class="text-muted" style="margin-bottom: 20px; font-weight: 500; font-size: 1.1rem;">You haven't organized any events yet.</p>
                <a href="create_event.php" class="btn btn-primary" style="width: auto;">Create Your First Event</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>