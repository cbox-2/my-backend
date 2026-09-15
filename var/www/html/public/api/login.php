<?php
require 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? ''); $password = $data['password'] ?? '';
if (!$username || !$password) { echo json_encode(['success'=>false,'message'=>'Credentials required']); exit; }
$stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?"); $stmt->execute([$username]); $user = $stmt->fetch();
if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username']; $_SESSION['role'] = $user['role'];
    echo json_encode(['success'=>true,'message'=>'Logged in','user'=>['id'=>$user['id'],'username'=>$user['username'],'role'=>$user['role']]]);
} else { echo json_encode(['success'=>false,'message'=>'Invalid credentials']); }