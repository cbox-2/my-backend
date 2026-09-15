<?php
require_once "../includes/config.php";
checkAuth();

if(isset($_GET["delete"]) && is_numeric($_GET["delete"])) {
    try {
        $pdo->prepare("DELETE FROM sys_users WHERE id=?")->execute([$_GET["delete"]]);
        $success = "تم حذف المستخدم";
    } catch(Exception $e) { $error = "خطأ في الحذف"; }
}

if($_SERVER["REQUEST_METHOD"] === "POST") {
    if(isset($_POST["add_user"])) {
        $u = trim($_POST["username"] ?? "");
        $e = trim($_POST["email"] ?? "");
        $p = $_POST["password"] ?? "";
        $st = $_POST["status"] ?? "pending";
        if($u && $e && $p) {
            $h = password_hash($p, PASSWORD_DEFAULT);
            try {
                $pdo->prepare("INSERT INTO sys_users(username,email,password_hash,status)VALUES(?,?,?,?)")->execute([$u,$e,$h,$st]);
                $success = "تم إضافة المستخدم";
            } catch(Exception $ex) { $error = "خطأ"; }
        }
    } elseif(isset($_POST["edit_user"])) {
        $id = $_POST["user_id"] ?? 0;
        $e = trim($_POST["email"] ?? "");
        $st = $_POST["status"] ?? "pending";
        $p = $_POST["password"] ?? "";
        if($id && $e) {
            try {
                if($p) {
                    $h = password_hash($p, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE sys_users SET email=?,password_hash=?,status=? WHERE id=?")->execute([$e,$h,$st,$id]);
                } else {
                    $pdo->prepare("UPDATE sys_users SET email=?,status=? WHERE id=?")->execute([$e,$st,$id]);
                }
                $success = "تم تعديل المستخدم";
            } catch(Exception $ex) { $error = "خطأ"; }
        }
    }
}

$users = $pdo->query("SELECT * FROM sys_users ORDER BY created_at DESC")->fetchAll();
$edit_user = null;
if(isset($_GET["edit"]) && is_numeric($_GET["edit"])) {
    $stmt = $pdo->prepare("SELECT * FROM sys_users WHERE id=?");
    $stmt->execute([$_GET["edit"]]);
    $edit_user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>المستخدمين</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<style>
body{background:#f4f6f9}
.sidebar{background:#2c3e50;color:#fff;min-height:100vh;padding:20px 0;position:fixed;width:250px}
.sidebar a{color:#ecf0f1;text-decoration:none;display:block;padding:12px 20px;margin:5px 10px;border-radius:8px}
.sidebar a:hover,.sidebar a.active{background:#34495e;color:#3498db}
.content{margin-right:250px;padding:30px}
.card-custom{border:none;border-radius:12px;box-shadow:0 2px 15px rgba(0,0,0,.08);margin-bottom:30px}
.card-header-custom{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:12px 12px 0 0;padding:15px 20px}
.btn-add{background:#667eea;border:none;color:#fff}
.btn-edit{background:#f59e0b;border:none;color:#fff}
.btn-delete{background:#ef4444;border:none;color:#fff}
.badge-status{padding:6px 12px;border-radius:20px}
.status-active{background:#10b981;color:#fff}
.status-banned{background:#ef4444;color:#fff}
.status-pending{background:#f59e0b;color:#fff}
</style>
</head>
<body>
<div class="sidebar">
<h4>🔧 SeaBox Admin</h4>
<a href="../dashboard.php"> الرئيسية</a>
<a href="users.php" class="active">👥 المستخدمين</a>
<a href="settings.php">⚙️ الإعدادات</a>
<a href="logs.php"> السجلات</a>
<a href="../logout.php" style="color:#ef4444;margin-top:50px">🚪 خروج</a>
</div>
<div class="content">
<h2 class="mb-4">👥 إدارة المستخدمين</h2>
<?php if(isset($success)): ?><div class="alert alert-success"><?=$success?></div><?php endif ?>
<?php if(isset($error)): ?><div class="alert alert-danger"><?=$error?></div><?php endif ?>

<div class="card card-custom">
<div class="card-header-custom"><?php if($edit_user): ?>✏️ تعديل<?php else: ?>➕ إضافة جديد<?php endif ?></div>
<div class="card-body">
<form method="POST">
<?php if($edit_user): ?><input type="hidden" name="user_id" value="<?=$edit_user["id"]?>"><?php endif ?>
<div class="row g-3">
<?php if(!$edit_user): ?><div class="col-md-3"><input type="text" name="username" class="form-control" placeholder="اسم المستخدم" required></div><?php endif ?>
<div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="البريد" value="<?=$edit_user["email"]??""?>" required></div>
<div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="كلمة المرور" <?=$edit_user?"":"required"?>></div>
<div class="col-md-2">
<select name="status" class="form-select">
<option value="pending" <?=($edit_user && $edit_user["status"]=="pending")?"selected":""?>>⏳ معلق</option>
<option value="active" <?=(!$edit_user || $edit_user["status"]=="active")?"selected":""?>>✅ نشط</option>
<option value="banned" <?=($edit_user && $edit_user["status"]=="banned")?"selected":""?>> محظور</option>
</select>
</div>
<div class="col-md-1"><button type="submit" name="<?=$edit_user?"edit_user":"add_user"?>" class="btn btn-add w-100"><?=$edit_user?"تعديل":"إضافة"?></button></div>
</div>
</form>
</div>
</div>

<div class="card card-custom">
<div class="card-header bg-white">📋 قائمة المستخدمين</div>
<div class="card-body">
<table class="table table-hover">
<thead><tr><th>ID</th><th>الاسم</th><th>البريد</th><th>الحالة</th><th>التاريخ</th><th>إجراءات</th></tr></thead>
<tbody>
<?php if(is_array($users)) foreach($users as $u): ?>
<tr>
<td><?=$u["id"]?></td>
<td><strong><?=htmlspecialchars($u["username"])?></strong></td>
<td><?=htmlspecialchars($u["email"])?></td>
<td><span class="badge-status status-<?=$u["status"]?>"><?=$u["status"]?></span></td>
<td><?=date("Y-m-d",strtotime($u["created_at"]))?></td>
<td>
<a href="?edit=<?=$u["id"]?>" class="btn btn-edit btn-sm">✏️</a>
<a href="?delete=<?=$u["id"]?>" class="btn btn-delete btn-sm" onclick="return confirm('متأكد؟')">🗑️</a>
</td>
</tr>
<?php endforeach ?>
<?php if(empty($users)): ?><tr><td colspan="6" class="text-center">لا يوجد مستخدمين</td></tr><?php endif ?>
</tbody>
</table>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>