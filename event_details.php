<?php
session_start();
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
    require_once 'includes/header.php';
    echo "<div class='card card-center'><div class='alert alert-error'>Invalid Event ID. <a href='index.php'>Go back</a>.</div></div></body></html>";
    exit;
}

$event_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT e.*, u.name AS organizer_name FROM Events e JOIN Users u ON e.organizer_id = u.user_id WHERE e.event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    require_once 'includes/header.php';
    echo "<div class='card card-center'><div class='alert alert-error'>Event not found. <a href='index.php'>Go back</a>.</div></div></body></html>";
    exit;
}

$ticketStmt = $pdo->prepare("SELECT COUNT(*) FROM Tickets WHERE event_id = ?");
$ticketStmt->execute([$event_id]);
$tickets_sold = $ticketStmt->fetchColumn();
$available_seats = $event['total_seats'] - $tickets_sold;

require_once 'includes/header.php'; 
?>
<div class="card" style="max-width: 800px; margin: 2rem auto; padding: 0; display: flex; flex-direction: column;">
    <div style="height: 300px; background-image: url('<?= htmlspecialchars($event['image_url'] ?? 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=800&h=400') ?>'); background-size: cover; background-position: center; border-radius: 20px 20px 0 0;"></div>
    <div style="padding: 2.5rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 5px;"><?= htmlspecialchars($event['title']) ?></h1>
    <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Organized by: <span class="text-accent"><?= htmlspecialchars($event['organizer_name']) ?></span></p>
    <hr>
    <div style="margin-bottom: 30px; font-size: 1.1rem; line-height: 1.8;">
        <h3 style="margin-bottom: 10px; font-size: 1.3rem;">📝 About this event:</h3>
        <p style="color: var(--text-muted);"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
        <div style="margin-top: 25px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border-left: 4px solid var(--primary);">
                <strong style="display: block; margin-bottom: 5px; color: var(--text-main);">📅 Date & Time</strong> 
                <?= date('F j, Y', strtotime($event['date'])) ?><br>
                <span class="text-muted"><?= date('g:i A', strtotime($event['date'])) ?></span>
            </div>
            <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; border-left: 4px solid var(--secondary);">
                <strong style="display: block; margin-bottom: 5px; color: var(--text-main);">📍 Location</strong> 
                <?= htmlspecialchars($event['location']) ?>
            </div>
        </div>
    </div>
    <div style="background: rgba(99, 102, 241, 0.05); padding: 25px; border-radius: 12px; border: 1px solid rgba(99, 102, 241, 0.2); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 1.2rem; font-weight: 600;">Tickets Available</span>
        <span style="font-size: 1.8rem; font-weight: 800; color: <?= $available_seats > 0 ? 'var(--secondary)' : 'var(--accent)' ?>;">
            <?= $available_seats ?> <span style="font-size: 1.1rem; color: var(--text-muted); font-weight: 500;">/ <?= $event['total_seats'] ?></span>
        </span>
    </div>
    <?php if ($available_seats > 0): ?>
        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="book_ticket.php" method="POST">
                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                <button type="submit" class="btn btn-primary" style="font-size: 1.2rem; padding: 18px; text-transform: uppercase; letter-spacing: 1px;">
                    Secure Your Ticket Now
                </button>
            </form>
        <?php else: ?>
            <div class="alert" style="background: rgba(255,255,255,0.05); text-align: center; display: flex; flex-direction: column; align-items: center; gap: 15px; border: 1px solid rgba(255,255,255,0.1);">
                <p style="margin: 0; font-weight: 500;">You must be logged in to book a ticket.</p>
                <div style="display: flex; gap: 10px;">
                    <a href="login.php" class="btn btn-outline" style="width: auto;">Log In</a>
                    <a href="register.php" class="btn btn-primary" style="width: auto;">Register</a>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <button disabled style="background: rgba(244, 63, 94, 0.1); color: var(--accent); padding: 18px; border: 2px dashed rgba(244, 63, 94, 0.3); border-radius: 12px; font-size: 1.2rem; width: 100%; cursor: not-allowed; font-weight: 800; text-transform: uppercase;">
            Event Sold Out
        </button>
    <?php endif; ?>
    </div>
</div>
</body>
</html>