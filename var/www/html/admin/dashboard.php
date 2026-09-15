<?php
require_once "includes/config.php";
if(!isset($_SESSION["admin_id"])) redirect("login.php");

$stats = [
    "admins" => $pdo->query("SELECT COUNT(*) FROM sys_admins")->fetchColumn(),
    "users" => $pdo->query("SELECT COUNT(*) FROM sys_users")->fetchColumn(),
    "logs" => $pdo->query("SELECT COUNT(*) FROM sys_logs")->fetchColumn(),
];
$recent_logs = $pdo->query("SELECT action, created_at FROM sys_logs ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لوحة التحكم</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<style>
body{background:#f4f6f9;font-family:"Segoe UI",Tahoma,sans-serif}
.sidebar{background:linear-gradient(180deg,#2c3e50,#34495e);color:#fff;min-height:100vh;padding:20px 0;position:fixed;width:250px}
.sidebar h4{padding:0 20px;margin-bottom:30px;font-weight:bold}
.sidebar a{color:#ecf0f1;text-decoration:none;display:block;padding:12px 20px;margin:5px 10px;border-radius:8px;transition:.3s}
.sidebar a:hover,.sidebar a.active{background:#34495e;color:#3498db}
.content{margin-right:250px;padding:30px}
.stat-box{background:#fff;padding:25px;border-radius:12px;box-shadow:0 2px 15px rgba(0,0,0,.08);text-align:center;margin-bottom:20px;transition:.3s}
.stat-box:hover{transform:translateY(-5px)}
.stat-box h2{margin:10px 0;font-weight:bold;font-size:2.5em}
.card-custom{border:none;border-radius:12px;box-shadow:0 2px 15px rgba(0,0,0,.08)}
.card-header-custom{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:12px 12px 0 0;padding:15px 20px;font-weight:bold}
</style>
</head>
<body>
<div class="sidebar">
<h4>🔧 SeaBox Admin</h4>
<a href="dashboard.php" class="active">🏠 الرئيسية</a>
<a href="pages/users.php">👥 المستخدمين</a>
<a href="pages/settings.php">⚙️ الإعدادات</a>
<a href="pages/logs.php">📜 السجلات</a>
<a href="logout.php" style="color:#ef4444;margin-top:50px">🚪 خروج</a>
</div>
<div class="content">
<h2 class="mb-2">مرحباً، <?= htmlspecialchars($_SESSION["admin_username"]) ?> 👋</h2>
<p class="text-muted">نظرة عامة على النظام</p>
<div class="row mt-4">
<div class="col-md-4"><div class="stat-box"><h5>👥 المديرين</h5><h2 class="text-primary"><?=$stats["admins"]?></h2></div></div>
<div class="col-md-4"><div class="stat-box"><h5>👤 المستخدمين</h5><h2 class="text-success"><?=$stats["users"]?></h2></div></div>
<div class="col-md-4"><div class="stat-box"><h5>📜 سجل الأحداث</h5><h2 class="text-info"><?=$stats["logs"]?></h2></div></div>
</div>
<div class="card card-custom mt-4">
<div class="card-header-custom">📋 آخر العمليات</div>
<div class="card-body">
<table class="table table-striped">
<thead><tr><th>العملية</th><th>الوقت</th></tr></thead>
<tbody>
<?php foreach($recent_logs as $log): ?>
<tr><td><?= htmlspecialchars($log["action"]) ?></td><td><?= $log["created_at"] ?></td></tr>
<?php endforeach; ?>
<?php if(empty($recent_logs)): ?>
<tr><td colspan="2" class="text-center text-muted">📭 لا توجد عمليات مسجلة بعد</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
