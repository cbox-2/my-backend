<?php
require_once __DIR__ . '/db.php';

if (!isAuthenticated()) {
    sendJsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'update') {
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    try {
        if ($email) {
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
        }
        
        if ($password && strlen($password) >= 6) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['user_id']]);
        }
        
        sendJsonResponse(['success' => true, 'message' => 'Account updated successfully']);
    } catch (PDOException $e) {
        sendJsonResponse(['success' => false, 'message' => 'Update failed'], 500);
    }
} else {
    sendJsonResponse(['success' => false, 'message' => 'Unknown action'], 400);
}