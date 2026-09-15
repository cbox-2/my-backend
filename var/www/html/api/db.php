<?php
/**
 * NexusBox API - Database Connection
 * ملف الاتصال بقاعدة البيانات
 * 
 * @package NexusBox
 * @version 1.0.2
 */

// بدء الجلسة للتحقق من المستخدمين
session_start();

// إعدادات الهيدر للأمان
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// إعدادات قاعدة البيانات
$db_host = 'localhost';
$db_name = 'nexusbox_db';
$db_user = 'nexusbox';
$db_pass = 'NexusBox2026';


// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // تسجيل الخطأ
    error_log("Database connection failed: " . $e->getMessage());
    
    // إرجاع خطأ JSON
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

/**
 * إرسال استجابة JSON
 */
function sendJsonResponse($data, $statusCode = 200) {
    if (ob_get_level()) {
        ob_clean();
    }
    
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * التحقق من تسجيل دخول المستخدم
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * الحصول على معرف المستخدم الحالي
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * الحصول على اسم المستخدم الحالي
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}