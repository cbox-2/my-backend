<?php
require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'save';

// ========== حفظ خيارات التكامل ==========
if ($action === 'save') {
    header('Content-Type: text/html; charset=utf-8');
    
    $enabled = isset($_POST['uo']) ? 1 : 0;
    $autoRegister = isset($_POST['uoreg']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE user_integration SET enabled = ?, auto_register = ? WHERE box_id = 1");
        $stmt->execute([$enabled, $autoRegister]);
        
        echo '<html><body><script>ld=1;parent.setmsg("fuserint","Integration options saved",1);parent.rcvdformresponse("t_fuserint");</script></body></html>';
    } catch (PDOException $e) {
        echo '<html><body><script>ld=1;parent.setmsg("fuserint","Error saving options",2);parent.rcvdformresponse("t_fuserint");</script></body></html>';
    }
    exit;
}

// ========== توليد مفتاح جديد ==========
if ($action === 'regen') {
    header('Content-Type: text/html; charset=utf-8');
    
    $newKey = substr(md5(uniqid(rand(), true)), 0, 16);
    
    try {
        $stmt = $pdo->prepare("UPDATE user_integration SET private_key = ? WHERE box_id = 1");
        $stmt->execute([$newKey]);
        
        echo '<html><body><script>ld=1;parent.setmsg("fuserint","New private key generated",1);parent.rcvdformresponse("t_fuserint");parent.location.reload();</script></body></html>';
    } catch (PDOException $e) {
        echo '<html><body><script>ld=1;parent.setmsg("fuserint","Error generating key",2);parent.rcvdformresponse("t_fuserint");</script></body></html>';
    }
    exit;
}

// ========== Default ==========
echo '<html><body><script>ld=1;parent.setmsg("fuserint","Invalid action",2);parent.rcvdformresponse("t_fuserint");</script></body></html>';
exit;