<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email is already registered. Please login.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            
            if ($insertStmt->execute([$name, $email, $hashed_password, $role])) {
                $success = 'Registration successful! You can now <a href="login.php" style="color: var(--mocha); text-decoration: underline; font-weight: bold;">login here</a>.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
require_once 'includes/header.php'; 
?>
<div class="card card-center">
    <h2 style="text-align: center; margin-bottom: 25px;">Join VeriTick</h2>
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
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="role">Account Type</label>
                <select name="role" id="role" class="custom-select">
                    <option value="user">I want to buy tickets</option>
                    <option value="admin">I want to organize events</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary" style="margin-top: 15px;">Create Account</button>
        </form>
    <?php endif; ?>
    <p style="text-align: center; margin-top: 25px; font-size: 0.95rem;" class="text-muted">
        Already have an account? <a href="login.php" class="text-success" style="text-decoration: none; font-weight: 700;">Log in</a>
    </p>
</div>
</body>
</html>