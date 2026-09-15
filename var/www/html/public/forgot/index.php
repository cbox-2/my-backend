<?php
session_start();
require_once __DIR__ . '/../../api/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);
            
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires]);
            
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/public/forgot/reset.php?token=" . $token;
            
            $subject = "Password Recovery - Cbox";
            $message = "Hello " . $user['username'] . ",\n\n";
            $message .= "You requested to reset your password.\n\n";
            $message .= "Click the following link to reset your password:\n";
            $message .= $resetLink . "\n\n";
            $message .= "This link will expire in 1 hour.\n\n";
            $message .= "If you did not request this, please ignore this email.\n\n";
            $message .= "If you have lost access to your email, please contact support.";
            
            @mail($email, $subject, $message, "From: noreply@" . $_SERVER['HTTP_HOST']);
        }
        
        $success = 'If your email is registered in our system, you will receive password reset instructions shortly.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Password Recovery · Cbox</title>
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

<h1>Password Recovery</h1>

<?php if ($error): ?>
<div class="notice_er">
<strong>Error:</strong> <?=htmlspecialchars($error)?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="notice_ok">
<strong>Success:</strong> <?=htmlspecialchars($success)?>
</div>
<?php endif; ?>

<?php if (!$success): ?>
<p>If you have forgotten your account name or password, enter your email address below and we will send you instructions for resetting your password.</p>

<form method="POST" action="">
<fieldset>
<label for="email">Email address:</label>
<input type="email" name="email" id="email" class="txtbox" value="<?=htmlspecialchars($_POST['email'] ?? '')?>" required>
<input type="submit" value="Request recovery">
</fieldset>
</form>

<p>If you have lost access to the email address you used, you will need to <a href="/public/admin/contact.php">contact us</a>.</p>
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