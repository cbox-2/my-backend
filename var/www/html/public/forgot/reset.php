<?php
session_start();
require_once __DIR__ . '/../../api/db.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (empty($token)) {
    header("Location: /public/forgot/");
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $error = 'Invalid or expired reset link. Please request a new password reset.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    if (empty($password) || empty($confirm)) {
        $error = 'Please fill in all fields';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        
        $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->execute([$token]);
        
        $success = 'Your password has been reset successfully. You can now login with your new password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Reset Password · Cbox</title>
<link rel="stylesheet" href="style.css">
<script src="code.js"></script>
</head>
<body>
<div id="mainbody">
<div id="bodywrap">
<div class="wrap">

<div id="header">
<div class="wrap">
<span id="title"><a href="/public/admin/"><img id="logo" src="/gfx/logo.png" alt="Cbox" width="120" height="50"></a></span>
<span id="headerExtras">
<a href="/public/admin/">Home</a>
<a href="/public/signup/">Sign up</a>
</span>
</div>
</div>

<div id="topbar">
<div class="wrap">
<div class="Spacer"></div>
</div>
</div>

<div id="subbar">
<div class="wrap">
</div>
</div>

<div id="content">
<div class="wrap">

<h1>Reset Password</h1>

<?php if ($error): ?>
<div class="notice_er">
<strong>Error:</strong> <?=htmlspecialchars($error)?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="notice_ok">
<strong>Success:</strong> <?=htmlspecialchars($success)?>
</div>
<p><a href="/public/admin/">Click here to login</a></p>
<?php elseif ($user): ?>
<p>Enter your new password below.</p>

<form method="POST" action="">
<input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">
<fieldset>
<label for="password">New Password:</label>
<input type="password" name="password" id="password" class="txtbox" required>

<label for="confirm">Confirm Password:</label>
<input type="password" name="confirm" id="confirm" class="txtbox" required>

<input type="submit" value="Reset Password">
</fieldset>
</form>
<?php else: ?>
<p><a href="/public/forgot/">Request a new password reset link</a></p>
<?php endif; ?>

</div>
</div>

</div>

<div id="footer">
<div class="wrap">
<a href="/public/admin/">About</a>
<a href="/public/admin/plans.php">Plans & pricing</a>
<a href="/public/admin/terms.php">Terms & conditions</a>
<a href="/public/admin/privacy.php">Privacy policy</a>
<a href="/public/admin/notices.php">Notices</a>
<a href="/public/admin/contact.php">Contact us</a>
<div class="verybottom">
&copy; 2004–2026 Cbox Communications (Pty) Ltd, all rights reserved.
</div>
</div>
</div>

</div>
</div>
</body>
</html>