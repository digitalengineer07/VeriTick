<?php
$host = 'localhost';
$dbname = 'veritick';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Auto-cleanup old events: Automatically delete events 48 hours after they finish
    $oldStmt = $pdo->query("SELECT event_id FROM Events WHERE date < DATE_SUB(NOW(), INTERVAL 48 HOUR)");
    $oldIds = $oldStmt->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($oldIds)) {
        $in = str_repeat('?,', count($oldIds) - 1) . '?';
        // Delete checkins related to the tickets of these events
        $pdo->prepare("DELETE FROM Checkins WHERE ticket_id IN (SELECT ticket_id FROM Tickets WHERE event_id IN ($in))")->execute($oldIds);
        // Delete the tickets
        $pdo->prepare("DELETE FROM Tickets WHERE event_id IN ($in)")->execute($oldIds);
        // Finally, delete the events themselves
        $pdo->prepare("DELETE FROM Events WHERE event_id IN ($in)")->execute($oldIds);
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>