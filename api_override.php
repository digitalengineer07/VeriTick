<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$ticket_id = (int)($input['ticket_id'] ?? 0);
$action = $input['action'] ?? ''; // 'reset' or 'checkin'

if (!$ticket_id || !in_array($action, ['reset', 'checkin'])) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$organizer_id = $_SESSION['user_id'];

// Check auth
$stmt = $pdo->prepare("SELECT e.organizer_id FROM Tickets t JOIN Events e ON t.event_id = e.event_id WHERE t.ticket_id = ?");
$stmt->execute([$ticket_id]);
$res = $stmt->fetch();

if (!$res || $res['organizer_id'] != $organizer_id) {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

if ($action === 'reset') {
    $stmt = $pdo->prepare("UPDATE Tickets SET used = 0, used_at = NULL, used_by_scanner_id = NULL WHERE ticket_id = ?");
} else {
    $stmt = $pdo->prepare("UPDATE Tickets SET used = 1, used_at = NOW(), used_by_scanner_id = ? WHERE ticket_id = ?");
}

if ($action === 'reset') {
    $stmt->execute([$ticket_id]);
} else {
    $stmt->execute([$organizer_id, $ticket_id]);
}

echo json_encode(['success' => true]);
