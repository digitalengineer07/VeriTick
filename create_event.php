<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("<div style='padding: 20px; color: red;'>Access Denied.</div>");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $image_url = trim($_POST['image_url']);
    $raw_date = $_POST['date'];

    if (empty($image_url)) {
        // default placeholder
        $image_url = 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=800&h=400';
    }
    $location = trim($_POST['location']);
    $total_seats = (int)$_POST['total_seats'];
    $organizer_id = $_SESSION['user_id'];

    if (empty($title) || empty($raw_date) || empty($location) || $total_seats <= 0) {
        $error = 'Please fill in all required fields correctly.';
    } else {
        try {
            $formatted_date = date('Y-m-d H:i:s', strtotime($raw_date));
            $stmt = $pdo->prepare("INSERT INTO Events (organizer_id, title, description, date, location, total_seats, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$organizer_id, $title, $description, $formatted_date, $location, $total_seats, $image_url])) {
                $success = 'Event created successfully! <a href="dashboard.php" style="color: var(--mocha); font-weight: bold;">Go to Dashboard</a>.';
            } else {
                $error = 'Failed to create event.';
            }
        } catch (PDOException $e) {
            $error = 'Database Error: ' . $e->getMessage();
        }
    }
}
require_once 'includes/header.php'; 
?>
<div class="card card-center" style="max-width: 650px; margin-top: 2rem;">
    <h2 style="margin-bottom: 25px; border-bottom: 2px dashed rgba(255,255,255,0.1); padding-bottom: 15px;">Launch a <span class="text-accent">New Event</span></h2>
    <?php if ($error): ?> 
        <div class="alert alert-error">
            <span style="font-size: 1.2rem; margin-right: 8px;">⚠️</span> <?= $error ?>
        </div> 
    <?php endif; ?>
    <?php if ($success): ?> 
        <div class="alert alert-success">
            <span style="font-size: 1.2rem; margin-right: 8px;">✨</span> <?= $success ?>
        </div> 
    <?php else: ?>
        <form method="POST" action="create_event.php">
            <div class="form-group">
                <label for="title">Event Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" required>
            </div>
            <div class="form-group">
                <label for="description">Event Description</label>
                <textarea name="description" id="description" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="image_url">Event Banner Image URL</label>
                <input type="url" name="image_url" id="image_url" placeholder="https://example.com/image.jpg">
            </div>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label for="date">Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="date" id="date" required>
                </div>
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label for="total_seats">Total Seats <span class="text-danger">*</span></label>
                    <input type="number" name="total_seats" id="total_seats" min="1" required>
                </div>
            </div>
            <div class="form-group">
                <label for="location">Location <span class="text-danger">*</span></label>
                <input type="text" name="location" id="location" required>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Publish Event</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>