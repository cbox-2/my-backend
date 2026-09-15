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
        echo '<html><body><script>parent.setmsg("fcban","No countries selected",2);parent.rcvdformresponse("t_fcban");</script></body></html>';
        exit;
    }
    
    // ========== حذف القيود ==========
    if ($act == 1) {
        $deleted = 0;
        foreach ($ids as $id) {
            $stmt = $pdo->prepare("DELETE FROM country_bans WHERE id = ?");
            $stmt->execute([$id]);
            $deleted++;
        }
        echo '<html><body><script>parent.setmsg("fcban","Removed '.$deleted.' country restriction(s)",1);parent.rcvdformresponse("t_fcban");location.href="admin_l_country_bans.php";</script></body></html>';
        exit;
    }
}

// ========== جلب البيانات ==========
$stmt = $pdo->query("SELECT * FROM country_bans ORDER BY country_name ASC");
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
.badge-block { background: #fc735a; color: #fff; }
.badge-allow { background: #a6d83f; color: #fff; }
</style>
</head>
<body>

<form name="cboxcountry" method="post" action="admin_l_country_bans.php">
<input type="hidden" name="act" value="">

<table>
<thead>
<tr>
    <th style="width:30px;"></th>
    <th>Country</th>
    <th>Code</th>
    <th>Policy</th>
    <th>Added By</th>
    <th>Added At</th>
</tr>
</thead>
<tbody>
<?php if (empty($bans)): ?>
<tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">No country restrictions found.</td></tr>
<?php else: ?>
<?php foreach ($bans as $ban): 
    $policyBadge = $ban['policy'] == 1 ? '<span class="badge badge-block">Block</span>' : '<span class="badge badge-allow">Allow Only</span>';
?>
<tr id="r<?php echo $ban['id']; ?>" onclick="t(<?php echo $ban['id']; ?>)">
    <td><input type="checkbox" name="z<?php echo $ban['id']; ?>" value="<?php echo $ban['id']; ?>" id="c<?php echo $ban['id']; ?>" onclick="event.stopPropagation();"></td>
    <td><?php echo htmlspecialchars($ban['country_name']); ?></td>
    <td><?php echo htmlspecialchars($ban['country_code']); ?></td>
    <td><?php echo $policyBadge; ?></td>
    <td><?php echo htmlspecialchars($ban['added_by'] ?? 'admin'); ?></td>
    <td><?php echo date('M j, Y H:i', strtotime($ban['added_at'])); ?></td>
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