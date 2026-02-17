<?php
/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I
 * API: Notifications
 * 
 * Endpoints (via ?action=...):
 *   GET  list          – All notifications for the logged-in user
 *   GET  unread_count  – Count of unread notifications
 *   POST mark_read     – Mark a notification as read
 *   POST mark_all_read – Mark all notifications as read
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

session_start();
if (empty($_SESSION['authenticated'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$action  = $_GET['action'] ?? '';
$userId  = $_SESSION['user']['user_id'] ?? 0;

function jsonOut($data) { echo json_encode($data); exit; }

switch ($action) {

    case 'list':
        $stmt = getDB()->prepare(
            "SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 30"
        );
        $stmt->execute([':uid' => $userId]);
        jsonOut(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

    case 'unread_count':
        $stmt = getDB()->prepare(
            "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = :uid AND is_read = 0"
        );
        $stmt->execute([':uid' => $userId]);
        jsonOut($stmt->fetch(PDO::FETCH_ASSOC));

    case 'mark_read':
        $body = json_decode(file_get_contents('php://input'), true);
        $nid  = intval($body['notification_id'] ?? 0);
        if ($nid) {
            $stmt = getDB()->prepare(
                "UPDATE notifications SET is_read = 1 WHERE notification_id = :nid AND user_id = :uid"
            );
            $stmt->execute([':nid' => $nid, ':uid' => $userId]);
        }
        jsonOut(['success' => true]);

    case 'mark_all_read':
        $stmt = getDB()->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0"
        );
        $stmt->execute([':uid' => $userId]);
        jsonOut(['success' => true, 'affected' => $stmt->rowCount()]);

    default:
        http_response_code(400);
        jsonOut(['error' => 'Invalid action']);
}
