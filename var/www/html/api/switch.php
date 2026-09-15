<?php
require_once __DIR__ . '/db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// ========== قائمة الصناديق ==========
if ($action === 'list') {
    $userId = $_SESSION['user_id'] ?? 1;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM boxes WHERE user_id = ? ORDER BY is_active DESC, last_used DESC");
        $stmt->execute([$userId]);
        $boxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'boxes' => $boxes]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ========== تبديل الصندوق النشط ==========
if ($action === 'switch') {
    $boxId = intval($_POST['box_id'] ?? 0);
    $userId = $_SESSION['user_id'] ?? 1;
    
    if ($boxId < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid box ID']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // إلغاء تفعيل جميع الصناديق
        $stmt = $pdo->prepare("UPDATE boxes SET is_active = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // تفعيل الصندوق المحدد
        $stmt = $pdo->prepare("UPDATE boxes SET is_active = 1, last_used = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$boxId, $userId]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Box switched successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ========== إنشاء صندوق جديد ==========
if ($action === 'create') {
    $name = trim($_POST['name'] ?? '');
    $userId = $_SESSION['user_id'] ?? 1;
    
    if (!$name) {
        echo json_encode(['success' => false, 'message' => 'Box name is required']);
        exit;
    }
    
    // توليد tag و boxid عشوائي
    $tag = substr(md5(uniqid(rand(), true)), 0, 6);
    $boxid = rand(1000000, 9999999);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO boxes (user_id, name, tag, boxid, plan, status, is_active, last_used) VALUES (?, ?, ?, ?, 'free', 'active', 0, NOW())");
        $stmt->execute([$userId, $name, $tag, $boxid]);
        
        echo json_encode(['success' => true, 'message' => 'Box created successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ========== Default ==========
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;