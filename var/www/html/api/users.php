<?php
require_once __DIR__ . '/db.php';
session_start();

// Session check disabled for testing
if (false && !isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// ========== قائمة المستخدمين ==========
if ($action === 'list') {
    header('Content-Type: text/html; charset=utf-8');
    $search = trim($_POST['srch'] ?? '');
    $searchIn = $_POST['srchin'] ?? 'nme';
    
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
        1 => 'System', 2 => 'Regular user', 3 => 'Moderator',
        4 => 'Administrator', 5 => 'Bot user'
    ];
    
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
    echo 'body{font-family:Open Sans,Tahoma,Arial,sans-serif;font-size:13px;margin:0;padding:0;background:transparent;}';
    echo 'table{width:100%;border-collapse:collapse;}';
    echo 'th{background:#f2f2f2;padding:6px 8px;text-align:left;border-bottom:2px solid #ddd;font-weight:600;}';
    echo 'td{padding:6px 8px;border-bottom:1px solid #eee;}';
    echo 'tr:hover{background:#fafafa;}';
    echo 'tr.selected{background:#e8f4f8;}';
    echo 'a{color:#059ad0;text-decoration:none;}';
    echo 'a:hover{text-decoration:underline;}';
    echo '.badge{display:inline-block;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;}';
    echo '.badge-admin{background:#fc735a;color:#fff;}';
    echo '.badge-mod{background:#fcc946;color:#fff;}';
    echo '.badge-user{background:#a6d83f;color:#fff;}';
    echo '.badge-bot{background:#999;color:#fff;}';
    echo '</style></head><body><table><thead><tr>';
    echo '<th style="width:30px;"></th><th>Username</th><th>Type</th><th>Email</th><th>Registered</th><th>Last Login</th><th>Voice</th>';
    echo '</tr></thead><tbody>';
    
    if (empty($users)) {
        echo '<tr><td colspan="7" style="text-align:center;padding:20px;color:#999;">No users found.</td></tr>';
    } else {
        foreach ($users as $user) {
            $levelName = $levelNames[$user['level']] ?? 'Unknown';
            $badgeClass = 'badge-user';
            if ($user['level'] == 3) $badgeClass = 'badge-mod';
            elseif ($user['level'] == 4) $badgeClass = 'badge-admin';
            elseif ($user['level'] == 5) $badgeClass = 'badge-bot';
            
            echo '<tr id="r'.$user['id'].'" onclick="t('.$user['id'].')">';
            echo '<td><input type="checkbox" name="x'.$user['id'].'" value="'.$user['id'].'" id="c'.$user['id'].'" onclick="event.stopPropagation();"></td>';
            echo '<td><a href="javascript:void(0)" onclick="event.stopPropagation(); parent.editUser('.$user['id'].')" style="color:#059ad0;text-decoration:underline;cursor:pointer;">'.htmlspecialchars($user['username']).'</a></td>';
            echo '<td><span class="badge '.$badgeClass.'">'.htmlspecialchars($levelName).'</span></td>';
            echo '<td>'.htmlspecialchars($user['email'] ?? '-').'</td>';
            echo '<td>'.date('M j, Y', strtotime($user['created_at'])).'</td>';
            echo '<td>'.($user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never').'</td>';
            echo '<td>'.($user['voice_status'] ? '✓' : '-').'</td>';
            echo '</tr>';
        }
    }
    
    echo '</tbody></table>';
    echo '<script>function t(id){var cb=document.getElementById("c"+id);if(cb)cb.checked=!cb.checked;var row=document.getElementById("r"+id);if(row){if(cb.checked)row.classList.add("selected");else row.classList.remove("selected");}}</script>';
    echo '</body></html>';
    exit;
}

// ========== جلب بيانات المستخدم ==========
if ($action === 'getuser') {
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_GET['id'] ?? 0);
    if ($id > 1) {
        $stmt = $pdo->prepare("SELECT id, username, email, level, created_at, last_login, last_ip FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    }
    exit;
}

// ========== تحميل بيانات المستخدم للتعديل ==========
if ($action === 'loadedit') {
    $uid = intval($_POST['uid'] ?? 0);
    if ($uid > 1) {
        $stmt = $pdo->prepare("SELECT id, username, email, level, created_at, last_login, last_ip FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo '<html><body><script>';
            echo 'parent.document.getElementById("edit_uid").value = "'.$user['id'].'";';
            echo 'parent.document.getElementById("edit_username_display").textContent = "'.htmlspecialchars($user['username']).'";';
            echo 'parent.document.getElementById("edit_email").value = "'.htmlspecialchars($user['email']).'";';
            echo 'parent.document.getElementById("edit_level").value = "'.$user['level'].'";';
            echo 'parent.document.getElementById("edit_created").textContent = "'.($user['created_at'] ?? 'Unknown').'";';
            echo 'parent.document.getElementById("edit_lastlogin").textContent = "'.($user['last_login'] ?? 'Never').'";';
            echo 'parent.document.getElementById("edit_lastip").textContent = "'.($user['last_ip'] ?? 'Unknown').'";';
            echo 'parent.document.getElementById("editUserForm").style.display = "block";';
            echo 'parent.document.getElementById("editUserForm").scrollIntoView({behavior: "smooth"});';
            echo '</script></body></html>';
        }
    }
    exit;
}

// ========== إضافة مستخدم ==========
if ($action === 'add') {
    header('Content-Type: text/html; charset=utf-8');
    $uname = trim($_POST['uname'] ?? '');
    $pword = $_POST['pword'] ?? '';
    $lvl = intval($_POST['lvl'] ?? 2);
    
    if (!$uname || !$pword) {
        echo '<html><body><script>ld=1;parent.setmsg("fuseradd","Username and password are required",2);parent.rcvdformresponse("t_fuseradd");</script></body></html>';
        exit;
    }
    
    if (strlen($uname) < 2 || strlen($uname) > 25) {
        echo '<html><body><script>ld=1;parent.setmsg("fuseradd","Username must be 2-25 characters",2);parent.rcvdformresponse("t_fuseradd");</script></body></html>';
        exit;
    }
    
    $role = ($lvl == 3) ? 'mod' : (($lvl == 4) ? 'admin' : (($lvl == 5) ? 'bot' : 'user'));
    $hashed = password_hash($pword, PASSWORD_BCRYPT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, level) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$uname, strtolower($uname).'@nexusbox.local', $hashed, $role, $lvl]);
        echo '<html><body><script>ld=1;parent.setmsg("fuseradd","User added successfully",1);parent.rcvdformresponse("t_fuseradd");parent.frames["cboxusers"].location.reload();</script></body></html>';
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo '<html><body><script>ld=1;parent.setmsg("fuseradd","Username already exists",2);parent.rcvdformresponse("t_fuseradd");</script></body></html>';
        } else {
            echo '<html><body><script>ld=1;parent.setmsg("fuseradd","Error adding user",2);parent.rcvdformresponse("t_fuseradd");</script></body></html>';
        }
    }
    exit;
}

// ========== تعديل المستخدم ==========
if ($action === 'edit') {
    header('Content-Type: text/html; charset=utf-8');
    $uid = intval($_POST['uid'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $level = intval($_POST['level'] ?? 2);
    $newPassword = $_POST['new_password'] ?? '';
    
    if ($uid < 2) {
        echo '<html><body><script>ld=1;parent.setmsg("fuseredit","Invalid user ID",2);parent.rcvdformresponse("t_fuseredit");</script></body></html>';
        exit;
    }
    
    if (!$email) {
        echo '<html><body><script>ld=1;parent.setmsg("fuseredit","Email is required",2);parent.rcvdformresponse("t_fuseredit");</script></body></html>';
        exit;
    }
    
    $role = ($level == 3) ? 'mod' : (($level == 4) ? 'admin' : 'user');
    
    try {
        if ($newPassword) {
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET email=?, level=?, role=?, password=? WHERE id=?");
            $stmt->execute([$email, $level, $role, $hashed, $uid]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET email=?, level=?, role=? WHERE id=?");
            $stmt->execute([$email, $level, $role, $uid]);
        }
        echo '<html><body><script>ld=1;parent.setmsg("fuseredit","User updated successfully",1);parent.rcvdformresponse("t_fuseredit");parent.frames["cboxusers"].location.reload();parent.closeEditForm();</script></body></html>';
    } catch (PDOException $e) {
        echo '<html><body><script>ld=1;parent.setmsg("fuseredit","Error updating user",2);parent.rcvdformresponse("t_fuseredit");</script></body></html>';
    }
    exit;
}

// ========== حذف مستخدم ==========
if ($action === 'delete' || (isset($_POST['act']) && $_POST['act'] == 2)) {
    header('Content-Type: text/html; charset=utf-8');
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) $ids = [$ids];
    
    $deleted = 0;
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id > 1) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $deleted++;
        }
    }
    
    echo '<html><body><script>ld=1;parent.setmsg("fuseropt","Deleted '.$deleted.' user(s)",1);parent.rcvdformresponse("t_fuseropt");parent.frames["cboxusers"].location.reload();</script></body></html>';
    exit;
}

// ========== تبديل Mod ==========
if ($action === 'mod' || (isset($_POST['act']) && $_POST['act'] == 3)) {
    header('Content-Type: text/html; charset=utf-8');
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) $ids = [$ids];
    
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id > 1) {
            $stmt = $pdo->prepare("SELECT level FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $newLevel = ($user['level'] == 3) ? 2 : 3;
                $newRole = ($newLevel == 3) ? 'mod' : 'user';
                $stmt = $pdo->prepare("UPDATE users SET level=?, role=? WHERE id=?");
                $stmt->execute([$newLevel, $newRole, $id]);
            }
        }
    }
    
    echo '<html><body><script>ld=1;parent.setmsg("fuseropt","Moderator status toggled",1);parent.rcvdformresponse("t_fuseropt");parent.frames["cboxusers"].location.reload();</script></body></html>';
    exit;
}

// ========== تبديل Admin ==========
if ($action === 'admin' || (isset($_POST['act']) && $_POST['act'] == 4)) {
    header('Content-Type: text/html; charset=utf-8');
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) $ids = [$ids];
    
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id > 1) {
            $stmt = $pdo->prepare("SELECT level FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $newLevel = ($user['level'] == 4) ? 2 : 4;
                $newRole = ($newLevel == 4) ? 'admin' : 'user';
                $stmt = $pdo->prepare("UPDATE users SET level=?, role=? WHERE id=?");
                $stmt->execute([$newLevel, $newRole, $id]);
            }
        }
    }
    
    echo '<html><body><script>ld=1;parent.setmsg("fuseropt","Admin status toggled",1);parent.rcvdformresponse("t_fuseropt");parent.frames["cboxusers"].location.reload();</script></body></html>';
    exit;
}

// ========== تبديل Voice ==========
if ($action === 'voice' || (isset($_POST['act']) && $_POST['act'] == 5)) {
    header('Content-Type: text/html; charset=utf-8');
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) $ids = [$ids];
    
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id > 1) {
            $stmt = $pdo->prepare("UPDATE users SET voice_status = 1 - voice_status WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    echo '<html><body><script>ld=1;parent.setmsg("fuseropt","Voice status toggled",1);parent.rcvdformresponse("t_fuseropt");parent.frames["cboxusers"].location.reload();</script></body></html>';
    exit;
}

// ========== خيارات المستخدمين ==========
if ($action === 'options') {
    header('Content-Type: text/html; charset=utf-8');
    $regonly = isset($_POST['regonly']) ? 1 : 0;
    $selfreg = isset($_POST['selfreg']) ? 1 : 0;
    $auth_fb = isset($_POST['auth_fb']) ? 1 : 0;
    $lastpostdel = isset($_POST['lastpostdel']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES ('regonly', ?), ('selfreg', ?), ('auth_fb', ?), ('lastpostdel', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute([$regonly, $selfreg, $auth_fb, $lastpostdel]);
        echo '<html><body><script>ld=1;parent.setmsg("fuseropt","Options saved successfully",1);parent.rcvdformresponse("t_fuseropt");</script></body></html>';
    } catch (PDOException $e) {
        echo '<html><body><script>ld=1;parent.setmsg("fuseropt","Error saving options",2);parent.rcvdformresponse("t_fuseropt");</script></body></html>';
    }
    exit;
}

// ========== Default: قائمة ==========
header('Content-Type: text/html; charset=utf-8');
echo '<html><body><script>parent.setmsg("fuseropt","Invalid action",2);parent.rcvdformresponse("t_fuseropt");</script></body></html>';