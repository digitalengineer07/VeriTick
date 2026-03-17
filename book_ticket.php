<?php
session_start();
require_once 'includes/db.php';
define('QR_SECRET_KEY', 'veritick_super_secret_key_2026');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$event_id = (int)$_POST['event_id'];

// Check if user already booked this event
$checkExisting = $pdo->prepare("SELECT ticket_id FROM Tickets WHERE user_id = ? AND event_id = ?");
$checkExisting->execute([$user_id, $event_id]);
if ($checkExisting->fetch()) {
    header("Location: my_tickets.php?status=exists");
    exit;
}

$stmt = $pdo->prepare("SELECT title, total_seats, date, location FROM Events WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    showStyledError("We couldn't find that event in our system.");
}

$ticketStmt = $pdo->prepare("SELECT COUNT(*) FROM Tickets WHERE event_id = ?");
$ticketStmt->execute([$event_id]);
$tickets_sold = $ticketStmt->fetchColumn();

if ($tickets_sold >= $event['total_seats']) {
    showStyledError("Too late! This event just sold out.");
}

if (isset($_POST['attendee_name']) && isset($_POST['attendee_contact'])) {
    $attendee_name = trim($_POST['attendee_name']);
    $attendee_contact = trim($_POST['attendee_contact']);
    
    if (empty($attendee_name) || empty($attendee_contact)) {
        showStyledError("Please fill in both name and contact.");
    }
    
    do {
        $qr_payload = "VT-" . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $checkQry = $pdo->prepare("SELECT ticket_id FROM Tickets WHERE qr_code = ?");
        $checkQry->execute([$qr_payload]);
    } while ($checkQry->fetch());
    $qr_signature = hash_hmac('sha256', $qr_payload, QR_SECRET_KEY);

    try {
        $insertStmt = $pdo->prepare("INSERT INTO Tickets (user_id, event_id, qr_code, qr_signature, attendee_name, attendee_contact) VALUES (?, ?, ?, ?, ?, ?)");
        $insertStmt->execute([$user_id, $event_id, $qr_payload, $qr_signature, $attendee_name, $attendee_contact]);
        header("Location: my_tickets.php?status=success");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            header("Location: my_tickets.php?status=exists");
            exit;
        }
        showStyledError("A database error occurred: " . $e->getMessage());
    }
}

// Show Data Collection Form
require_once 'includes/header.php';
?>
<div class="card card-center" style="max-width: 500px; margin-top: 4rem;">
    <h2 style="margin-bottom: 25px; border-bottom: 2px dashed rgba(255,255,255,0.1); padding-bottom: 15px;">Complete <span class="text-accent">Registration</span></h2>
    <p class="text-muted" style="margin-bottom: 10px;">You are securing a ticket for:</p>
    <h3 class="text-main" style="margin-bottom: 5px;"><?= htmlspecialchars($event['title']) ?></h3>
    <p style="font-size: 0.95rem; margin-bottom: 25px;">
        <strong class="text-success">📅</strong> <?= date('F j, Y', strtotime($event['date'])) ?>
    </p>

    <form method="POST" action="book_ticket.php">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">
        <div class="form-group">
            <label for="attendee_name">Attendee Name <span class="text-danger">*</span></label>
            <input type="text" name="attendee_name" id="attendee_name" value="<?= htmlspecialchars($_SESSION['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="attendee_contact">Contact Phone/Email <span class="text-danger">*</span></label>
            <input type="text" name="attendee_contact" id="attendee_contact" required placeholder="e.g. +1 555-0123">
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top: 15px; font-size: 1.1rem; padding: 16px;">Generate Digital Ticket</button>
    </form>
</div>
</body>
</html>
<?php
exit;

function showStyledError($error_message) {
    if (!headers_sent()) {
        require_once 'includes/header.php';
    }
    echo '<div class="card card-center" style="text-align: center; margin-top: 4rem;">';
    echo '<h2 class="text-danger" style="margin-bottom: 20px;">Booking Failed</h2>';
    echo '<div class="alert alert-error" style="margin-bottom: 25px;">' . htmlspecialchars($error_message) . '</div>';
    echo '<a href="index.php" class="btn btn-outline" style="width: auto;">Browse Other Events</a>';
    echo '</div></body></html>';
    exit;
}
?>