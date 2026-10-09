<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode     = $_POST['mode'] ?? 'login';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in every field.';
    } elseif ($mode === 'register') {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['user'] = ['id' => (int)$pdo->lastInsertId(), 'username' => $username];
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'That username is already taken.';
        }
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = ['id' => (int)$user['id'], 'username' => $user['username']];
            header('Location: index.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TaskTrack · Sign in</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Mochiy+Pop+One&display=swap" rel="stylesheet">
<script>
  const savedTheme = localStorage.getItem('tasktrack-theme');
  if (savedTheme === 'light' || savedTheme === 'dark') {
    document.documentElement.dataset.theme = savedTheme;
  }
</script>
<link rel="stylesheet" href="assets/style.css?v=48">
</head>
<body class="auth">
<div class="auth-card">
  <div class="brand big"><span class="brand-name"><span class="brand-task"><span class="brand-t">T</span><span class="brand-a">a</span><span class="brand-s">s</span><span class="brand-k">k</span></span><span class="brand-track">Track</span></span></div>
  <p class="auth-sub">Your day, tracked.</p>
  <?php if ($error): ?><div class="auth-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="mode" id="mode" value="login">
    <label>Username <input name="username" required maxlength="50" autofocus></label>
    <label>Password <input type="password" name="password" required></label>
    <button class="btn primary block" id="auth-submit">Sign in</button>
  </form>
  <p class="auth-switch">New here? <a href="#" id="auth-toggle">Create an account</a></p>
</div>
<script>
const toggle = document.getElementById('auth-toggle');
const mode = document.getElementById('mode');
const submit = document.getElementById('auth-submit');
toggle.addEventListener('click', e => {
  e.preventDefault();
  const registering = mode.value !== 'register';
  mode.value = registering ? 'register' : 'login';
  submit.textContent = registering ? 'Create account' : 'Sign in';
  toggle.textContent = registering ? 'Sign in instead' : 'Create an account';
});
</script>
</body>
</html>
