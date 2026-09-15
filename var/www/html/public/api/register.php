<?php
require 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? ''); $email = trim($data['email'] ?? ''); $password = $data['password'] ?? '';
if (!$username || !$email || !$password) { echo json_encode(['success'=>false,'message'=>'All fields required']); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success'=>false,'message'=>'Invalid email']); exit; }
if (strlen($password) < 6) { echo json_encode(['success'=>false,'message'=>'Password min 6 chars']); exit; }
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?"); $stmt->execute([$username, $email]);
if ($stmt->fetch()) { echo json_encode(['success'=>false,'message'=>'Username or email exists']); exit; }
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
try { $stmt->execute([$username, $email, $hash]); $_SESSION['user_id'] = $pdo->lastInsertId(); $_SESSION['username'] = $username; $_SESSION['role'] = 'user';
echo json_encode(['success'=>true,'message'=>'Registered','user'=>['id'=>$_SESSION['user_id'],'username'=>$username,'role'=>'user']]); }
catch (PDOException $e) { echo json_encode(['success'=>false,'message'=>'Registration failed']); }