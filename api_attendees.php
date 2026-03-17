<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['event_id'])) {
    echo json_encode([]);
    exit;
}

$event_id = (int)$_GET['event_id'];
$organizer_id = $_SESSION['user_id'];

// Check auth
$stmt = $pdo->prepare("SELECT event_id FROM Events WHERE event_id = ? AND organizer_id = ?");
$stmt->execute([$event_id, $organizer_id]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT t.ticket_id, t.attendee_name, t.attendee_contact, t.used, t.used_at, t.qr_code, u.name as buyer_name
    FROM Tickets t
    LEFT JOIN Users u ON t.user_id = u.user_id
    WHERE t.event_id = ?
    ORDER BY t.used_at DESC, t.ticket_id DESC
");
$stmt->execute([$event_id]);
$attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($attendees);
