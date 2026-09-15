<?php
require_once __DIR__ . '/db.php';

if (!isAuthenticated()) {
    sendJsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
}

try {
    // Get total messages count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages");
    $totalPosts = $stmt->fetch()['total'] ?? 0;
    
    // Get views (placeholder)
    $totalViews = 0;
    $recentViews = 0;
    $recentPosts = 0;
    $clients = 0;
    
    sendJsonResponse([
        'success' => true,
        'total_posts' => (int)$totalPosts,
        'total_views' => (int)$totalViews,
        'posts' => (int)$recentPosts,
        'views' => (int)$recentViews,
        'clients' => (int)$clients,
        'max_posts' => 1000,
        'max_views' => 1000,
        'max_clients' => 100,
        'period' => '7d',
        'created' => date('d M Y', strtotime($_SESSION['created_at'] ?? 'now')),
        'near_limit' => false
    ]);
} catch (PDOException $e) {
    sendJsonResponse(['success' => false, 'message' => 'Error'], 500);
}