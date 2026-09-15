<?php
require_once __DIR__ . '/db.php';

if (!isAuthenticated()) {
    sendJsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'lock':
        sendJsonResponse(['success' => true, 'message' => 'Lock status updated']);
        break;
    case 'close':
        sendJsonResponse(['success' => true, 'message' => 'NexusBox closed']);
        break;
    case 'quicklink':
        sendJsonResponse(['success' => true, 'message' => 'Quick link saved']);
        break;
    case 'boxname':
        sendJsonResponse(['success' => true, 'message' => 'Name updated']);
        break;
    default:
        sendJsonResponse(['success' => false, 'message' => 'Unknown action'], 400);
}