<?php
require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// ========== عرض قائمة المحظورين ==========
if ($action === 'list') {
    header('Content-Type: text/html; charset=utf-8');
    
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
    
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
    echo 'body{font-family:Open Sans,Tahoma,Arial,sans-serif;font-size:13px;margin:0;padding:0;background:transparent;}';
    echo 'table{width:100%;border-collapse:collapse;}';
    echo 'th{background:#f2f2f2;padding:6px 8px;text-align:left;border-bottom:2px solid #ddd;font-weight:600;}';
    echo 'td{padding:6px 8px;border-bottom:1px solid #eee;}';
    echo 'tr:hover{background:#fafafa;}';
    echo 'tr.selected{background:#e8f4f8;}';
    echo '.badge{display:inline-block;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;}';
    echo '.badge-active{background:#fc735a;color:#fff;}';
    echo '.badge-expired{background:#999;color:#fff;}';
    echo '.badge-strong{background:#fcc946;color:#fff;}';
    echo '</style></head><body>';
    echo '<form name="cboxbans" method="post" action="admin_l_bans.php">';
    echo '<input type="hidden" name="act" value="">';
    echo '<input type="hidden" name="parm" value="">';
    echo '<table><thead><tr>';
    echo '<th style="width:30px;"></th>';
    echo '<th>IP Address</th>';
    echo '<th>Username</th>';
    echo '<th>Reason</th>';
    echo '<th>Banned By</th>';
    echo '<th>Banned At</th>';
    echo '<th>Expires</th>';
    echo '<th>Status</th>';
    echo '</tr></thead><tbody>';
    
    if (empty($bans)) {
        echo '<tr><td colspan="8" style="text-align:center;padding:20px;color:#999;">No bans found.</td></tr>';
    } else {
        foreach ($bans as $ban) {
            $isExpired = $ban['expires_at'] && strtotime($ban['expires_at']) < time();
            $statusBadge = $isExpired ? '<span class="badge badge-expired">Expired</span>' : '<span class="badge badge-active">Active</span>';
            if ($ban['is_strong']) {
                $statusBadge .= ' <span class="badge badge-strong">Strong</span>';
            }
            
            echo '<tr id="r'.$ban['id'].'" onclick="t('.$ban['id'].')">';
            echo '<td><input type="checkbox" name="z'.$ban['id'].'" value="'.$ban['id'].'" id="c'.$ban['id'].'" onclick="event.stopPropagation();"></td>';
            echo '<td>'.htmlspecialchars($ban['ip_address']).'</td>';
            echo '<td>'.htmlspecialchars($ban['username'] ?? '-').'</td>';
            echo '<td>'.htmlspecialchars($ban['reason'] ?? '-').'</td>';
            echo '<td>'.htmlspecialchars($ban['banned_by'] ?? 'admin').'</td>';
            echo '<td>'.date('M j, Y H:i', strtotime($ban['banned_at'])).'</td>';
            echo '<td>'.($ban['expires_at'] ? date('M j, Y H:i', strtotime($ban['expires_at'])) : 'Never').'</td>';
            echo '<td>'.$statusBadge.'</td>';
            echo '</tr>';
        }
    }
    
    echo '</tbody></table>';
    echo '</form>';
    echo '<script>function t(id){var cb=document.getElementById("c"+id);if(cb)cb.checked=!cb.checked;var row=document.getElementById("r"+id);if(row){if(cb.checked)row.classList.add("selected");else row.classList.remove("selected");}}</script>';
    echo '</body></html>';
    exit;
}

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

// ========== إضافة حظر IP ==========
if ($action === 'add') {
    header('Content-Type: text/html; charset=utf-8');
    $ip = trim($_POST['ip'] ?? '');
    $duration = trim($_POST['dur'] ?? '');
    $reason = trim($_POST['ref'] ?? '');
    
    if (!$ip) {
        echo '<html><body><script>ld=1;parent.setmsg("fban","IP address is required",2);parent.rcvdformresponse("t_fban");</script></body></html>';
        exit;
    }
    
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        echo '<html><body><script>ld=1;parent.setmsg("fban","Invalid IP address",2);parent.rcvdformresponse("t_fban");</script></body></html>';
        exit;
    }
    
    $expiresAt = null;
    if ($duration) {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $duration));
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO bans (ip_address, reason, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$ip, $reason, $expiresAt]);
        echo '<html><body><script>ld=1;parent.setmsg("fban","IP banned successfully",1);parent.rcvdformresponse("t_fban");parent.frames["cboxbans"].location.reload();</script></body></html>';
    } catch (PDOException $e) {
        echo '<html><body><script>ld=1;parent.setmsg("fban","Error adding ban",2);parent.rcvdformresponse("t_fban");</script></body></html>';
    }
    exit;
}

// ========== Default: قائمة ==========
header('Location: admin_l_bans.php');
exit;