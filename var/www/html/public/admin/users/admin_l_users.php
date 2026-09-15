<?php
require_once __DIR__ . '/../../../api/db.php';

// ========== معالجة الأفعال ==========
if (isset($_POST['act'])) {
    $act = intval($_POST['act']);
    $ids = [];
    
    // جمع المعرفات المحددة
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'x') === 0) {
            $ids[] = intval($value);
        }
    }
    
    if (empty($ids)) {
        echo '<html><body><script>parent.setmsg("fuseropt","No users selected",2);parent.rcvdformresponse("t_fuseropt");</script></body></html>';
        exit;
    }
    
    // ========== حذف ==========
    if ($act == 2) {
        $deleted = 0;
        foreach ($ids as $id) {
            if ($id > 1) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $deleted++;
            }
        }
        echo '<html><body><script>parent.setmsg("fuseropt","Deleted '.$deleted.' user(s)",1);parent.rcvdformresponse("t_fuseropt");location.href="admin_l_users.php";</script></body></html>';
        exit;
    }
    
    // ========== تبديل Mod ==========
    if ($act == 3) {
        foreach ($ids as $id) {
            if ($id > 1) {
                $stmt = $pdo->prepare("SELECT level FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                if ($user) {
                    $newLevel = ($user['level'] == 3) ? 2 : 3;
                    $newRole = ($newLevel == 3) ? 'mod' : 'user';
                    $stmt = $pdo->prepare("UPDATE users SET level=?, role=? WHERE id=?");
                    $stmt->execute([$newLevel, $newRole, $id]);
                }
            }
        }
        echo '<html><body><script>parent.setmsg("fuseropt","Moderator status toggled",1);parent.rcvdformresponse("t_fuseropt");location.href="admin_l_users.php";</script></body></html>';
        exit;
    }
    
    // ========== تبديل Admin ==========
    if ($act == 4) {
        foreach ($ids as $id) {
            if ($id > 1) {
                $stmt = $pdo->prepare("SELECT level FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                if ($user) {
                    $newLevel = ($user['level'] == 4) ? 2 : 4;
                    $newRole = ($newLevel == 4) ? 'admin' : 'user';
                    $stmt = $pdo->prepare("UPDATE users SET level=?, role=? WHERE id=?");
                    $stmt->execute([$newLevel, $newRole, $id]);
                }
            }
        }
        echo '<html><body><script>parent.setmsg("fuseropt","Admin status toggled",1);parent.rcvdformresponse("t_fuseropt");location.href="admin_l_users.php";</script></body></html>';
        exit;
    }
    
    // ========== تبديل Voice ==========
    if ($act == 5) {
        foreach ($ids as $id) {
            if ($id > 1) {
                $stmt = $pdo->prepare("UPDATE users SET voice_status = 1 - voice_status WHERE id = ?");
                $stmt->execute([$id]);
            }
        }
        echo '<html><body><script>parent.setmsg("fuseropt","Voice status toggled",1);parent.rcvdformresponse("t_fuseropt");location.href="admin_l_users.php";</script></body></html>';
        exit;
    }
}

// ========== جلب البيانات ==========
$search = trim($_GET['srch'] ?? '');
$searchIn = $_GET['srchin'] ?? 'nme';

$sql = "SELECT id, username, email, level, role, created_at, last_login, last_ip, voice_status FROM users WHERE id > 1";
$params = [];

if ($search) {
    $fieldMap = [
        'nme' => 'username',
        'status' => 'level',
        'regged' => 'created_at',
        'lastip' => 'last_ip',
        'lastused' => 'last_login'
    ];
    $field = $fieldMap[$searchIn] ?? 'username';
    $sql .= " AND $field LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY username ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$levelNames = [
    1 => 'System',
    2 => 'Regular user',
    3 => 'Moderator',
    4 => 'Administrator',
    5 => 'Bot user'
];
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style>
body { font-family: 'Open Sans', Tahoma, Arial, sans-serif; font-size: 13px; margin: 0; padding: 0; background: transparent; }
table { width: 100%; border-collapse: collapse; }
th { background: #f2f2f2; padding: 6px 8px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; }
td { padding: 6px 8px; border-bottom: 1px solid #eee; }
tr:hover { background: #fafafa; }
tr.selected { background: #e8f4f8; }
a { color: #059ad0; text-decoration: none; }
a:hover { text-decoration: underline; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; }
.badge-admin { background: #fc735a; color: #fff; }
.badge-mod { background: #fcc946; color: #fff; }
.badge-user { background: #a6d83f; color: #fff; }
.badge-bot { background: #999; color: #fff; }
</style>
</head>
<body>

<!-- النموذج يجب أن يكون هنا ويحتوي على جميع checkboxes -->
<form name="cboxusers" method="post" action="admin_l_users.php">
<input type="hidden" name="act" value="">
<input type="hidden" name="parm" value="">

<table>
<thead>
<tr>
    <th style="width:30px;"></th>
    <th>Username</th>
    <th>Type</th>
    <th>Email</th>
    <th>Registered</th>
    <th>Last Login</th>
    <th>Voice</th>
</tr>
</thead>
<tbody>
<?php if (empty($users)): ?>
<tr><td colspan="7" style="text-align:center; padding:20px; color:#999;">No users found.</td></tr>
<?php else: ?>
<?php foreach ($users as $user): 
    $levelName = $levelNames[$user['level']] ?? 'Unknown';
    $badgeClass = 'badge-user';
    if ($user['level'] == 3) $badgeClass = 'badge-mod';
    elseif ($user['level'] == 4) $badgeClass = 'badge-admin';
    elseif ($user['level'] == 5) $badgeClass = 'badge-bot';
?>
<tr id="r<?php echo $user['id']; ?>" onclick="t(<?php echo $user['id']; ?>)">
    <td><input type="checkbox" name="x<?php echo $user['id']; ?>" value="<?php echo $user['id']; ?>" id="c<?php echo $user['id']; ?>" onclick="event.stopPropagation();"></td>
    <td><a href="javascript:void(0)" onclick="event.stopPropagation(); parent.editUser(<?php echo $user['id']; ?>)" style="color:#059ad0;text-decoration:underline;cursor:pointer;"><?php echo htmlspecialchars($user['username']); ?></a></td>
    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($levelName); ?></span></td>
    <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
    <td><?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
    <td><?php echo $user['voice_status'] ? '✓' : '-'; ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</form>

<script>
function t(id) {
    var cb = document.getElementById('c' + id);
    if (cb) cb.checked = !cb.checked;
    var row = document.getElementById('r' + id);
    if (row) {
        if (cb.checked) row.classList.add('selected');
        else row.classList.remove('selected');
    }
}
</script>
</body>
</html>