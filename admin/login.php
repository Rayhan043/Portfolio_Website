<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // Basic brute-force throttling
    $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
    $_SESSION['first_attempt_time'] = $_SESSION['first_attempt_time'] ?? time();
    if ($_SESSION['login_attempts'] >= 6 && (time() - $_SESSION['first_attempt_time']) < 300) {
        $error = 'Too many attempts. Please wait a few minutes and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['last_activity'] = time();
            unset($_SESSION['login_attempts'], $_SESSION['first_attempt_time']);
            redirect('dashboard.php');
        } else {
            $_SESSION['login_attempts']++;
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login · ML Command Center</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh;">
<div class="bg-gradient"></div>
<div class="glass panel" style="width:360px; position:relative; z-index:2;">
  <div class="section-tag">Restricted Access</div>
  <h2 class="section-title" style="margin-top:8px; font-size:22px;">Admin Login</h2>

  <?php if (isset($_GET['timeout'])): ?><div class="alert alert-error" style="margin-top:16px;">Session expired. Please log in again.</div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error" style="margin-top:16px;"><?= e($error) ?></div><?php endif; ?>

  <form method="POST" style="margin-top:18px;">
    <?php csrf_field(); ?>
    <div class="form-group"><label>Username</label><input class="form-control" type="text" name="username" required autofocus></div>
    <div class="form-group"><label>Password</label><input class="form-control" type="password" name="password" required></div>
    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Sign In</button>
  </form>
</div>
</body>
</html>
