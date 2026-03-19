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
            $organizer_code = null;
            $linked_organizer_id = null;
            
            if ($role === 'user') {
                $provided_code = trim($_POST['organizer_code'] ?? '');
                if (empty($provided_code)) {
                    $error = 'Please provide an Organizer Access Code to register as a user.';
                } else {
                    $codeStmt = $pdo->prepare("SELECT user_id FROM Users WHERE role = 'admin' AND organizer_code = ?");
                    $codeStmt->execute([$provided_code]);
                    $admin = $codeStmt->fetch();
                    if ($admin) {
                        $linked_organizer_id = $admin['user_id'];
                    } else {
                        $error = 'Invalid Organizer Access Code. Please check with your event organizer.';
                    }
                }
            } else {
                do {
                    $organizer_code = 'ORG-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
                    $checkQry = $pdo->prepare("SELECT user_id FROM Users WHERE organizer_code = ?");
                    $checkQry->execute([$organizer_code]);
                } while ($checkQry->fetch());
            }

            if (!$error) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, role, organizer_code, linked_organizer_id) VALUES (?, ?, ?, ?, ?, ?)");
                
                if ($insertStmt->execute([$name, $email, $hashed_password, $role, $organizer_code, $linked_organizer_id])) {
                    $success = 'Registration successful! You can now <a href="login.php" style="color: var(--mocha); text-decoration: underline; font-weight: bold;">login here</a>.';
                } else {
                    $error = 'Something went wrong. Please try again.';
                }
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
            <span style="font-size: 1.2rem; margin-right: 8px;">⚠️</span> <span><?= $error ?></span>
        </div> 
    <?php endif; ?>
    <?php if ($success): ?> 
        <div class="alert alert-success">
            <span style="font-size: 1.2rem; margin-right: 8px;">✨</span> <span><?= $success ?></span>
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
            <div class="form-group" id="organizer-code-group">
                <label for="organizer_code">Organizer Access Code <span class="text-danger">*</span></label>
                <input type="text" name="organizer_code" id="organizer_code" placeholder="e.g. ORG-XXXXXX">
                <small class="text-muted">Ask your event organizer for their unique code to access their events.</small>
            </div>
            <button type="submit" class="btn btn-secondary" style="margin-top: 15px;">Create Account</button>
        </form>
    <?php endif; ?>
    <p style="text-align: center; margin-top: 25px; font-size: 0.95rem;" class="text-muted">
        Already have an account? <a href="login.php" class="text-success" style="text-decoration: none; font-weight: 700;">Log in</a>
    </p>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const roleSelect = document.getElementById('role');
        const orgCodeGroup = document.getElementById('organizer-code-group');
        const orgCodeInput = document.getElementById('organizer_code');
        
        function toggleCodeField() {
            if (roleSelect.value === 'user') {
                orgCodeGroup.style.display = 'block';
                orgCodeInput.required = true;
            } else {
                orgCodeGroup.style.display = 'none';
                orgCodeInput.required = false;
            }
        }
        
        roleSelect.addEventListener('change', toggleCodeField);
        toggleCodeField(); // init
    });
</script>
</body>
</html>