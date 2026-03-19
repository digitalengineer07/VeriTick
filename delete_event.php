<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];
    $organizer_id = $_SESSION['user_id'];
    
    // Verify the admin owns this event (only organizers can delete their own events)
    $stmt = $pdo->prepare("SELECT * FROM Events WHERE event_id = ? AND organizer_id = ?");
    $stmt->execute([$event_id, $organizer_id]);
    $event = $stmt->fetch();
    
    if ($event) {
        try {
            $pdo->beginTransaction();
            
            // Delete associated tickets first because of foreign key constraints (if any)
            $ticketStmt = $pdo->prepare("DELETE FROM Tickets WHERE event_id = ?");
            $ticketStmt->execute([$event_id]);
            
            // Delete the event
            $delStmt = $pdo->prepare("DELETE FROM Events WHERE event_id = ?");
            $delStmt->execute([$event_id]);
            
            $pdo->commit();
            
            header("Location: my_events.php?msg=deleted");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error deleting event: " . $e->getMessage());
        }
    } else {
        die("Event not found or permission denied.");
    }
} else {
    header("Location: my_events.php");
    exit;
}
