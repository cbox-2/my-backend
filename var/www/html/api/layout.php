<?php
require_once __DIR__ . '/db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ========== تحميل إعدادات Layout ==========
if ($action === 'load') {
    $stmt = $pdo->query("SELECT chat_width, chat_height, form_height, form_auto_height, form_adaptive, form_above, form_narrow, language, messages_per_page, sort_direction FROM box_settings WHERE box_id = 1 LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        echo json_encode(['success' => false, 'message' => 'No settings found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'settings' => $settings]);
    exit;
}

// ========== حفظ إعدادات Layout ==========
if ($action === 'save') {
    $chatWidth = intval($_POST['twidth'] ?? 400);
    $chatHeight = intval($_POST['theight'] ?? 400);
    $formHeight = intval($_POST['fheight'] ?? 107);
    $formAutoHeight = isset($_POST['fhauto']) ? 1 : 0;
    $formAdaptive = isset($_POST['flines']) ? 1 : 0;
    $formAbove = isset($_POST['fontop']) ? 1 : 0;
    $formNarrow = isset($_POST['falt']) ? 1 : 0;
    $language = trim($_POST['lang'] ?? 'en');
    $messagesPerPage = intval($_POST['mpp'] ?? 20);
    $sortDirection = intval($_POST['sortdir'] ?? 1);
    
    // التحقق من القيم
    if ($chatWidth < 150 || $chatWidth > 5000) {
        echo json_encode(['success' => false, 'message' => 'Width must be between 150 and 5000']);
        exit;
    }
    if ($chatHeight < 76 || $chatHeight > 5000) {
        echo json_encode(['success' => false, 'message' => 'Height must be between 76 and 5000']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE box_settings SET 
            chat_width = ?, 
            chat_height = ?, 
            form_height = ?, 
            form_auto_height = ?, 
            form_adaptive = ?, 
            form_above = ?, 
            form_narrow = ?, 
            language = ?, 
            messages_per_page = ?, 
            sort_direction = ?
            WHERE box_id = 1");
        $stmt->execute([$chatWidth, $chatHeight, $formHeight, $formAutoHeight, $formAdaptive, $formAbove, $formNarrow, $language, $messagesPerPage, $sortDirection]);
        
        echo json_encode(['success' => true, 'message' => 'Layout settings saved']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ========== Default ==========
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;