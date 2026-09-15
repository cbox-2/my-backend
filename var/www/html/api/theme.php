<?php
require_once __DIR__ . '/db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ========== تحميل الثيم الحالي ==========
if ($action === 'load') {
    $stmt = $pdo->query("SELECT theme_name, theme_data, custom_css, font_family, font_size, font_size_sec, icon_size FROM box_settings WHERE box_id = 1 LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        echo json_encode(['success' => false, 'message' => 'No settings found']);
        exit;
    }
    
    $themeData = json_decode($settings['theme_data'] ?? '{}', true);
    
    echo json_encode([
        'success' => true,
        'theme' => [
            'name' => $settings['theme_name'] ?? 'default',
            'colors' => $themeData['colors'] ?? [],
            'fonts' => [
                'family' => $settings['font_family'] ?? 'Arial, sans-serif',
                'size' => $settings['font_size'] ?? '14px',
                'sizeSec' => $settings['font_size_sec'] ?? '75%',
                'iconSize' => $settings['icon_size'] ?? '14px'
            ],
            'customCSS' => $settings['custom_css'] ?? ''
        ]
    ]);
    exit;
}

// ========== حفظ الثيم ==========
if ($action === 'save') {
    $themeName = trim($_POST['theme_name'] ?? 'My Custom Theme');
    $colors = $_POST['colors'] ?? '{}';
    $fontFamily = trim($_POST['font_family'] ?? 'Arial, sans-serif');
    $fontSize = trim($_POST['font_size'] ?? '14px');
    $fontSizeSec = trim($_POST['font_size_sec'] ?? '75%');
    $iconSize = trim($_POST['icon_size'] ?? '14px');
    $customCSS = $_POST['custom_css'] ?? '';
    
    // بناء theme_data
    $themeData = json_encode([
        'colors' => json_decode($colors, true),
        'fonts' => [
            'family' => $fontFamily,
            'size' => $fontSize,
            'sizeSec' => $fontSizeSec,
            'iconSize' => $iconSize
        ]
    ]);
    
    try {
        $stmt = $pdo->prepare("UPDATE box_settings SET 
            theme_name = ?, 
            theme_data = ?, 
            custom_css = ?, 
            font_family = ?, 
            font_size = ?, 
            font_size_sec = ?, 
            icon_size = ?
            WHERE box_id = 1");
        $stmt->execute([$themeName, $themeData, $customCSS, $fontFamily, $fontSize, $fontSizeSec, $iconSize]);
        
        echo json_encode(['success' => true, 'message' => 'Theme saved successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error saving theme: ' . $e->getMessage()]);
    }
    exit;
}

// ========== حفظ الثيم كقالب ==========
if ($action === 'save_template') {
    $name = trim($_POST['name'] ?? '');
    $colors = $_POST['colors'] ?? '{}';
    $fonts = $_POST['fonts'] ?? '{}';
    $customCSS = $_POST['custom_css'] ?? '';
    
    if (!$name) {
        echo json_encode(['success' => false, 'message' => 'Theme name is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO themes (box_id, name, colors, fonts, custom_css) VALUES (1, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE colors = VALUES(colors), fonts = VALUES(fonts), custom_css = VALUES(custom_css)");
        $stmt->execute([$name, $colors, $fonts, $customCSS]);
        
        echo json_encode(['success' => true, 'message' => 'Theme template saved']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ========== قائمة الثيمات المحفوظة ==========
if ($action === 'list_templates') {
    $stmt = $pdo->query("SELECT id, name, colors, fonts, custom_css, created_at FROM themes WHERE box_id = 1 ORDER BY name ASC");
    $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'themes' => $themes]);
    exit;
}

// ========== تحميل ثيم محفوظ ==========
if ($action === 'load_template') {
    $id = intval($_GET['id'] ?? 0);
    if ($id < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid theme ID']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM themes WHERE id = ? AND box_id = 1");
    $stmt->execute([$id]);
    $theme = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$theme) {
        echo json_encode(['success' => false, 'message' => 'Theme not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'theme' => [
            'name' => $theme['name'],
            'colors' => json_decode($theme['colors'], true),
            'fonts' => json_decode($theme['fonts'], true),
            'customCSS' => $theme['custom_css']
        ]
    ]);
    exit;
}

// ========== حذف ثيم محفوظ ==========
if ($action === 'delete_template') {
    $id = intval($_POST['id'] ?? 0);
    if ($id < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid theme ID']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM themes WHERE id = ? AND box_id = 1");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Theme deleted']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ========== Default ==========
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;