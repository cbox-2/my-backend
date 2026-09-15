<?php
require_once __DIR__ . '/../../../api/db.php';

// ========== معالجة الأفعال ==========
if (isset($_POST['act'])) {
    $act = intval($_POST['act']);
    $ids = [];
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'z') === 0) {
            $ids[] = intval($value);
        }
    }
    
    if (empty($ids)) {
        echo '<html><body><script>parent.setmsg("fban","No bans selected",2);parent.rcvdformresponse("t_fban");</script></body></html>';
        exit;
    }
    
    // ========== إلغاء الحظر ==========
    if ($act == 1) {
        $deleted = 0;
        foreach ($ids as $id) {
            $stmt = $pdo->prepare("DELETE FROM bans WHERE id = ?");
            $stmt->execute([$id]);
            $deleted++;
        }
        echo '<html><body><script>parent.setmsg("fban","Unbanned '.$deleted.' user(s)",1);parent.rcvdformresponse("t_fban");location.href="admin_l_bans.php";</script></body></html>';
        exit;
    }
    
    // ========== تقوية الحظر ==========
    if ($act == 2) {
        foreach ($ids as $id) {
            $stmt = $pdo->prepare("UPDATE bans SET is_strong = 1 WHERE id = ?");
            $stmt->execute([$id]);
        }
        echo '<html><body><script>parent.setmsg("fban","Bans strengthened",1);parent.rcvdformresponse("t_fban");location.href="admin_l_bans.php";</script></body></html>';
        exit;
    }
    
    // ========== تغيير تاريخ الانتهاء ==========
    if ($act == 3) {
        $duration = $_POST['parm'] ?? '4 weeks';
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $duration));
        
        foreach ($ids as $id) {
            $stmt = $pdo->prepare("UPDATE bans SET expires_at = ? WHERE id = ?");
            $stmt->execute([$expiresAt, $id]);
        }
        echo '<html><body><script>parent.setmsg("fban","Expiry updated to '.$duration.'",1);parent.rcvdformresponse("t_fban");location.href="admin_l_bans.php";</script></body></html>';
        exit;
    }
}

// ========== جلب البيانات ==========
$search = trim($_GET['srch'] ?? '');
$searchIn = $_GET['srchin'] ?? 'nme';

$sql = "SELECT * FROM bans WHERE 1=1";
$params = [];

if ($search) {
    $fieldMap = [
        'nme' => 'ip_address',
        'ref' => 'reason',
        'applied' => 'banned_at',
        'expiry' => 'expires_at'
    ];
    $field = $fieldMap[$searchIn] ?? 'ip_address';
    $sql .= " AND $field LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY banned_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bans = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
.badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; }
.badge-active { background: #fc735a; color: #fff; }
.badge-expired { background: #999; color: #fff; }
.badge-strong { background: #fcc946; color: #fff; }
</style>
</head>
<body>

<form name="cboxbans" method="post" action="admin_l_bans.php">
<input type="hidden" name="act" value="">
<input type="hidden" name="parm" value="">

<table>
<thead>
<tr>
    <th style="width:30px;"></th>
    <th>IP Address</th>
    <th>Username</th>
    <th>Reason</th>
    <th>Banned By</th>
    <th>Banned At</th>
    <th>Expires</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
<?php if (empty($bans)): ?>
<tr><td colspan="8" style="text-align:center; padding:20px; color:#999;">No bans found.</td></tr>
<?php else: ?>
<?php foreach ($bans as $ban): 
    $isExpired = $ban['expires_at'] && strtotime($ban['expires_at']) < time();
    $statusBadge = $isExpired ? '<span class="badge badge-expired">Expired</span>' : '<span class="badge badge-active">Active</span>';
    if ($ban['is_strong']) {
        $statusBadge .= ' <span class="badge badge-strong">Strong</span>';
    }
?>
<tr id="r<?php echo $ban['id']; ?>" onclick="t(<?php echo $ban['id']; ?>)">
    <td><input type="checkbox" name="z<?php echo $ban['id']; ?>" value="<?php echo $ban['id']; ?>" id="c<?php echo $ban['id']; ?>" onclick="event.stopPropagation();"></td>
    <td><?php echo htmlspecialchars($ban['ip_address']); ?></td>
    <td><?php echo htmlspecialchars($ban['username'] ?? '-'); ?></td>
    <td><?php echo htmlspecialchars($ban['reason'] ?? '-'); ?></td>
    <td><?php echo htmlspecialchars($ban['banned_by'] ?? 'admin'); ?></td>
    <td><?php echo date('M j, Y H:i', strtotime($ban['banned_at'])); ?></td>
    <td><?php echo $ban['expires_at'] ? date('M j, Y H:i', strtotime($ban['expires_at'])) : 'Never'; ?></td>
    <td><?php echo $statusBadge; ?></td>
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