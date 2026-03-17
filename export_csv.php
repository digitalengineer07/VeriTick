<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$event_id = (int)$_GET['id'];
$organizer_id = $_SESSION['user_id'];

// Check permission
$stmt = $pdo->prepare("SELECT title FROM Events WHERE event_id = ? AND organizer_id = ?");
$stmt->execute([$event_id, $organizer_id]);
$event = $stmt->fetch();

if (!$event) {
    die("Unauthorized or event not found.");
}

$filename = "GuestList_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $event['title']) . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Ticket ID', 'Raw QR Payload', 'Attendee Name', 'Contact Info', 'Booking Date', 'Entry Status', 'Check-in Time']);

$ticketsStmt = $pdo->prepare("
    SELECT ticket_id, qr_code, attendee_name, attendee_contact, created_at, used, used_at 
    FROM Tickets 
    WHERE event_id = ? 
    ORDER BY created_at ASC
");
$ticketsStmt->execute([$event_id]);

while ($row = $ticketsStmt->fetch(PDO::FETCH_ASSOC)) {
    $status = $row['used'] ? 'Checked In' : 'Pending';
    $used_at = $row['used_at'] ? date('Y-m-d H:i:s', strtotime($row['used_at'])) : 'N/A';
    fputcsv($output, [
        $row['ticket_id'],
        $row['qr_code'],
        $row['attendee_name'],
        $row['attendee_contact'],
        date('Y-m-d H:i:s', strtotime($row['created_at'])),
        $status,
        $used_at
    ]);
}
fclose($output);
exit;
