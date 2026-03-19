<?php 
require_once 'includes/db.php';
require_once 'includes/header.php'; 

$events = [];
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM Events WHERE organizer_id = ? ORDER BY date ASC");
        $stmt->execute([$_SESSION['user_id']]);
        $events = $stmt->fetchAll();
    } elseif (isset($_SESSION['linked_organizer_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM Events WHERE organizer_id = ? ORDER BY date ASC");
        $stmt->execute([$_SESSION['linked_organizer_id']]);
        $events = $stmt->fetchAll();
    }
}
?>
<section class="hero">
    <h1>Experience the <span class="text-accent">Future</span></h1>
    <p>Secure, fast, and digital ticketing. Find your next experience and book instantly.</p>
</section>

<div class="grid" style="margin-top: 2rem;">
    <?php if (count($events) > 0): ?>
        <?php foreach ($events as $event): ?>
            <div class="card" style="padding: 0; display: flex; flex-direction: column;">
                <div style="height: 160px; background-image: url('<?= htmlspecialchars($event['image_url'] ?? 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=800&h=400') ?>'); background-size: cover; background-position: center;"></div>
                <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                    <?php 
                        $event_ts = strtotime($event['date']);
                        $now = time();
                        $status_html = '';
                        if ($event_ts > $now + 86400) {
                            $status_html = '<span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); padding: 4px 8px; border-radius: 6px; font-size: 0.8rem;">Upcoming</span>';
                        } elseif ($event_ts > $now && $event_ts <= $now + 86400) {
                            $status_html = '<span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); padding: 4px 8px; border-radius: 6px; font-size: 0.8rem;">Starting Soon</span>';
                        } elseif ($event_ts <= $now && $now <= $event_ts + 86400) {
                            $status_html = '<span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16,185,129,0.3); padding: 4px 8px; border-radius: 6px; font-size: 0.8rem;">Ongoing</span>';
                        } else {
                            $status_html = '<span class="badge badge-neutral" style="padding: 4px 8px; border-radius: 6px; font-size: 0.8rem;">Completed</span>';
                        }
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem;">
                        <h3 style="font-size: 1.4rem; margin: 0; flex-grow: 1; margin-right: 10px;"><?= htmlspecialchars($event['title']) ?></h3>
                        <?= $status_html ?>
                    </div>
                    <div>
                        <div class="event-card-date">
                            <span>📅</span> <?= date('M j, Y • g:i a', strtotime($event['date'])) ?>
                        </div>
                        <div class="event-card-location">
                            <span>📍</span> <?= htmlspecialchars($event['location']) ?>
                        </div>
                        <a href="event_details.php?id=<?= $event['event_id'] ?>" class="btn btn-primary" style="margin-top: auto;">View Details</a>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && $_SESSION['user_id'] == $event['organizer_id']): ?>
                            <form action="delete_event.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this event? This action cannot be undone.');" style="margin-top: 10px;">
                                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                <button type="submit" class="btn" style="width: 100%; padding: 10px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.3); border-radius: 8px; font-weight: bold; cursor: pointer;">Delete Event</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem;">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <p class="text-muted" style="font-size: 1.2rem;">Please log in or create an account with an Organizer Access Code to view your events.</p>
                <a href="login.php" class="btn btn-primary" style="margin-top: 15px; width: auto; display: inline-block;">Log In</a>
            <?php else: ?>
                <p class="text-muted" style="font-size: 1.2rem;">No upcoming events right now. Check back soon!</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</div>
</body>
</html>