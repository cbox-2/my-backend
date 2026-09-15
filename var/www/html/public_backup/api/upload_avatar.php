<?php
session_start();
header('Content-Type: application/json');

// تحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Must be logged in']);
    exit;
}

$uploadDir = __DIR__ . '/../chat/uploads/avatars/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['avatar'];
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) { // 2MB max
    echo json_encode(['success' => false, 'message' => 'File too large']);
    exit;
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newName = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
$dest = $uploadDir . $newName;

if (move_uploaded_file($file['tmp_name'], $dest)) {
    $url = '/public/chat/uploads/avatars/' . $newName;
    // تحديث قاعدة البيانات (اختياري)
    // $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$url, $_SESSION['user_id']]);
    echo json_encode(['success' => true, 'url' => $url]);
} else {
    echo json_encode(['success' => false, 'message' => 'Upload failed']);
}