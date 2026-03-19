<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            if (isset($user['linked_organizer_id'])) {
                $_SESSION['linked_organizer_id'] = $user['linked_organizer_id'];
            }
            if ($user['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
require_once 'includes/header.php'; 
?>
<div class="card card-center">
    <h2 style="text-align: center; margin-bottom: 25px;">Welcome Back</h2>
    <?php if ($error): ?> 
        <div class="alert alert-error">
            <span style="font-size: 1.2rem; margin-right: 8px;">⚠️</span> <span><?= $error ?></span>
        </div> 
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Sign In</button>
    </form>
    <p style="text-align: center; margin-top: 25px; font-size: 0.95rem;" class="text-muted">
        New here? <a href="register.php" class="text-accent" style="text-decoration: none; font-weight: 700;">Create an account</a>
    </p>
</div>
</body>
</html>