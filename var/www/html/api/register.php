<?php
/**
 * NexusBox API - User Registration
 * معالجة إنشاء حساب مستخدم جديد
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
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';

// التحقق من الحقول المطلوبة
if (!$username || !$email || !$password) {
    sendJsonResponse(['success' => false, 'message' => 'All fields are required: username, email, password'], 400);
}

// التحقق من صحة اسم المستخدم (3-50 حرف، حروف وأرقام و _ - . فقط)
if (!preg_match('/^[a-zA-Z0-9_\-\.]{3,50}$/', $username)) {
    sendJsonResponse(['success' => false, 'message' => 'Username must be 3-50 characters (letters, numbers, underscores, hyphens, or dots only)'], 400);
}

// التحقق من صحة البريد الإلكتروني
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(['success' => false, 'message' => 'Please enter a valid email address'], 400);
}

// التحقق من قوة كلمة المرور (6 أحرف على الأقل)
if (strlen($password) < 6) {
    sendJsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters long'], 400);
}

// التحقق مما إذا كان اسم المستخدم أو البريد مسجل مسبقاً
try {
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) LIMIT 1");
    $stmt->execute([$username, $email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        if (strtolower($existing['username']) === strtolower($username)) {
            sendJsonResponse(['success' => false, 'message' => 'This username is already taken. Please choose another.'], 409);
        }
        if (strtolower($existing['email']) === strtolower($email)) {
            sendJsonResponse(['success' => false, 'message' => 'This email is already registered. Please use a different email or login.'], 409);
        }
    }
} catch (PDOException $e) {
    error_log('Registration check failed: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Registration check failed. Please try again.'], 500);
}

// تشفير كلمة المرور باستخدام bcrypt
$passwordHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
if ($passwordHash === false) {
    sendJsonResponse(['success' => false, 'message' => 'Password hashing failed. Please try again.'], 500);
}

// إدخال المستخدم الجديد في قاعدة البيانات
try {
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, avatar, role, created_at) 
        VALUES (:username, :email, :password, :avatar, :role, NOW())
    ");
    
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $passwordHash,
        ':avatar' => '', // صورة افتراضية فارغة
        ':role' => 'user' // الدور الافتراضي: مستخدم عادي
    ]);
    
    $newUserId = $pdo->lastInsertId();
    
    // بدء جلسة للمستخدم الجديد
    $_SESSION['user_id'] = $newUserId;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'user';
    $_SESSION['login_time'] = time();
    
    // تسجيل العملية
    error_log("User registered: ID=$newUserId, Username=$username, IP=" . $_SERVER['REMOTE_ADDR']);
    
    // إرسال الاستجابة الناجحة
    sendJsonResponse([
        'success' => true,
        'message' => 'Registration successful! Welcome to NexusBox.',
        'user' => [
            'id' => (int)$newUserId,
            'username' => $username,
            'email' => $email,
            'role' => 'user',
            'avatar' => '',
            'created_at' => date('c')
        ]
    ], 201);
    
} catch (PDOException $e) {
    error_log('Registration failed: ' . $e->getMessage());
    
    if ($e->getCode() == 23000) { // تكرار في البيانات
        sendJsonResponse(['success' => false, 'message' => 'Registration failed: username or email already exists'], 409);
    }
    
    sendJsonResponse(['success' => false, 'message' => 'Registration failed. Please try again.'], 500);
}