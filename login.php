<?php
session_start();
require_once 'includes/db.php';

if(isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Check if it's a password_hash, plain text, or SHA256 hash
        if(password_verify($password, $row['password']) || $password === $row['password'] || hash('sha256', $password) === $row['password']) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blood Bank System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="ph-fill ph-drop" style="font-size: 4rem;"></i>
                <h2>Welcome Back</h2>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">Sign in to manage the blood bank</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="ph-fill ph-warning-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="admin">
                </div>
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="admin123">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Sign In <i class="ph ph-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
