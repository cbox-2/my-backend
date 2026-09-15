<?php
/**
 * NexusBox API - Session Check
 * التحقق من حالة جلسة المستخدم
 */

require_once __DIR__ . '/db.php';

// السماح لطلبات GET و POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// التحقق مما إذا كان المستخدم مسجّل دخول
if (!isAuthenticated()) {
    sendJsonResponse([
        'success' => true,
        'logged_in' => false
    ], 200);
}

// المستخدم مسجّل دخول ← جلب بياناته من قاعدة البيانات
$user = getCurrentUser($pdo);

if (!$user) {
    // الجلسة موجودة لكن المستخدم غير موجود (ربما حُذف)
    $_SESSION = [];
    session_destroy();
    
    sendJsonResponse([
        'success' => true,
        'logged_in' => false,
        'message' => 'Session expired. Please login again.'
    ], 200);
}

// (اختياري) التحقق من عنوان IP للجلسة لمزيد من الأمان
/*
if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    error_log("Session IP mismatch: User ID={$_SESSION['user_id']}, Expected IP={$_SESSION['ip_address']}, Actual IP=" . $_SERVER['REMOTE_ADDR']);
    $_SESSION = [];
    session_destroy();
    sendJsonResponse(['success' => true, 'logged_in' => false, 'message' => 'Security check failed. Please login again.'], 200);
}
*/

// (اختياري) التحقق من عمر الجلسة
/*
$sessionTimeout = 3600; // 1 ساعة
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $sessionTimeout) {
    $_SESSION = [];
    session_destroy();
    sendJsonResponse(['success' => true, 'logged_in' => false, 'message' => 'Session expired due to inactivity. Please login again.'], 200);
}
*/

// إرسال بيانات المستخدم (بدون معلومات حساسة)
sendJsonResponse([
    'success' => true,
    'logged_in' => true,
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'avatar' => $user['avatar']
    ]
], 200);