<?php
/**
 * NexusBox API - User Login
 * معالجة تسجيل دخول المستخدم
 */

require_once __DIR__ . '/db.php';

// السماح فقط لطلبات POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// قراءة بيانات JSON المرسلة
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid JSON input'], 400);
}

// استخراج وتنظيف المدخلات
$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? $input['password'] : '';

// التحقق من الحقول المطلوبة
if (!$username || !$password) {
    sendJsonResponse(['success' => false, 'message' => 'Username and password are required'], 400);
}

// البحث عن المستخدم في قاعدة البيانات
try {
    $stmt = $pdo->prepare("SELECT id, username, password, email, role, avatar FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Login query failed: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Login failed. Please try again.'], 500);
}

// التحقق من كلمة المرور
if (!$user || !password_verify($password, $user['password'])) {
    // تسجيل محاولة الدخول الفاشلة (للمراقبة الأمنية)
    error_log("Failed login attempt for: $username - IP: " . $_SERVER['REMOTE_ADDR']);
    
    sendJsonResponse(['success' => false, 'message' => 'Invalid username or password'], 401);
}

// كلمة المرور صحيحة ← تحديث آخر دخول
try {
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
} catch (PDOException $e) {
    error_log('Failed to update last_login: ' . $e->getMessage());
}

// تجديد معرّف الجلسة لمنع تثبيت الجلسة
session_regenerate_id(true);

// حفظ بيانات المستخدم في الجلسة
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['login_time'] = time();
$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

// تسجيل الدخول الناجح
error_log("Successful login: User ID={$user['id']}, Username={$user['username']}, IP=" . $_SERVER['REMOTE_ADDR']);

// إرسال الاستجابة الناجحة
sendJsonResponse([
    'success' => true,
    'message' => 'Login successful! Welcome, ' . htmlspecialchars($user['username']) . '.',
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'avatar' => $user['avatar']
    ]
], 200);