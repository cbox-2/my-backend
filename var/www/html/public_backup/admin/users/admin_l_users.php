<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/db.php';

$search = $_GET['srch'] ?? '';
$searchIn = $_GET['srchin'] ?? 'nme';

$sql = "SELECT id, username, level, created_at FROM users WHERE id > 1";
$params = [];

if ($search) {
    $fieldMap = ['nme'=>'username','status'=>'level','regged'=>'created_at'];
    $field = $fieldMap[$searchIn] ?? 'username';
    $sql .= " AND $field LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY username ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html><head>
<title>NexusBox Registered Users</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="style.css">
<script type="text/javascript">
sel = new Array();
function t(id) {
  if (!document.getElementById("r"+id)) return false;
  if (sel[id] == null) {
    sel[id] = document.getElementById("r"+id).style.backgroundColor;
    document.getElementById("r"+id).style.backgroundColor="#e9eff3";
    document.getElementById("c"+id).checked = true;
  } else {
    document.getElementById("r"+id).style.backgroundColor=sel[id];
    document.getElementById("c"+id).checked = false;
    sel[id] = null;
  }
}
</script>
</head>
<body class="embList" style="overflow:auto;">
<form name="users" action="/api/users.php" method="post">
<input type="hidden" name="act" value="0">
<input type="hidden" name="parm" value="">

<div align="right">Page: -</div>

<table width="100%" cellspacing="1" cellpadding="2" border="0">
<tbody><tr><td width="20">&nbsp;</td>
<td><a href="/api/users.php?action=list&srch=&srchin=">Name</a></td>
<td>Token</td>
<td width="85" align="right"><a href="/api/users.php?action=list&srch=&srchin=">Registered</a></td>
</tr>
<?php if (empty($users)): ?>
<tr><td colspan="4" align="center"><i>No registered names found</i></td></tr>
<?php else: ?>
<?php foreach ($users as $user): 
    $levelName = ['2'=>'User','3'=>'Moderator','4'=>'Administrator','5'=>'Bot'][$user['level']] ?? 'User';
?>
<tr id="r<?php echo $user['id']; ?>" onclick="t(<?php echo $user['id']; ?>)">
<td><input type="checkbox" name="x<?php echo $user['id']; ?>" value="<?php echo $user['id']; ?>" id="c<?php echo $user['id']; ?>"></td>
<td><?php echo htmlspecialchars($user['username']); ?></td>
<td><?php echo $levelName; ?></td>
<td align="right"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody></table>

<div align="right">Page: -</div>

</form>
<script>ld=1;</script>
</body></html>