<?php
/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I
 * API: Visitor Management Module
 * 
 * Endpoints (via ?action=...):
 *   GET  list_visitors          – Visitor registry
 *   GET  list_logs              – Visit logs
 *   GET  list_preregistrations  – Pre-registrations
 *   GET  dashboard_stats        – Summary counts
 *   POST register_visitor       – New visitor
 *   POST check_in               – Check-in a visitor
 *   POST check_out              – Check-out a visitor
 *   POST preregister            – Pre-register a visitor
 *   POST approve_prereg         – Approve and auto-register a pre-registration
 *   POST reject_prereg          – Reject a pre-registration
 */

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
require_once __DIR__ . '/../config/audit.php';

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'list_visitors':
        $rows = getDB()->query("SELECT * FROM visitors ORDER BY created_at DESC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'list_logs':
        $status = $_GET['status'] ?? null;
        $sql = "SELECT vl.*, CONCAT(v.first_name,' ',v.last_name) AS visitor_name, v.company,
                       CONCAT(u.first_name,' ',u.last_name) AS host_user_name
                FROM visitor_logs vl
                JOIN visitors v ON vl.visitor_id = v.visitor_id
                LEFT JOIN users u ON vl.host_user_id = u.user_id";
        $params = [];
        if ($status) { $sql .= " WHERE vl.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY vl.check_in_time DESC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'list_preregistrations':
        $rows = getDB()->query("SELECT p.*, CONCAT(u.first_name,' ',u.last_name) AS host_name
            FROM visitor_preregistrations p
            JOIN users u ON p.host_user_id = u.user_id
            ORDER BY p.expected_date DESC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'register_visitor':
        $d = readJsonBody();
        $code = 'VIS-' . str_pad(random_int(1,999999),6,'0',STR_PAD_LEFT);
        $stmt = getDB()->prepare("INSERT INTO visitors 
            (visitor_code, first_name, last_name, email, phone, company, id_type, id_number)
            VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $code, $d['first_name'], $d['last_name'], $d['email'] ?? null,
            $d['phone'] ?? null, $d['company'] ?? null,
            $d['id_type'] ?? null, $d['id_number'] ?? null
        ]);
        logAudit('visitors', 'REGISTER_VISITOR', 'visitors', null, null, ['visitor_code' => $code, 'name' => ($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? '')]);
        jsonResponse(['success' => true, 'visitor_code' => $code], 201);

    case 'check_in':
        $d = readJsonBody();
        $visitCode = 'VL-' . date('Y') . '-' . str_pad(random_int(1,999999),6,'0',STR_PAD_LEFT);
        $stmt = getDB()->prepare("INSERT INTO visitor_logs 
            (visit_code, visitor_id, host_user_id, host_name, host_department, purpose,
             purpose_details, check_in_time, badge_number, status, created_by)
            VALUES (?,?,?,?,?,?,?,NOW(),?,'checked_in',?)");
        $stmt->execute([
            $visitCode, $d['visitor_id'], $d['host_user_id'] ?? null,
            $d['host_name'] ?? null, $d['host_department'] ?? null,
            $d['purpose'], $d['purpose_details'] ?? null,
            $d['badge_number'] ?? null, $d['created_by'] ?? 1
        ]);
        // Update visit count
        getDB()->prepare("UPDATE visitors SET visit_count = visit_count + 1 WHERE visitor_id = ?")->execute([$d['visitor_id']]);
        logAudit('visitors', 'CHECK_IN', 'visitor_logs', null, null, ['visit_code' => $visitCode, 'visitor_id' => $d['visitor_id']]);
        jsonResponse(['success' => true, 'visit_code' => $visitCode], 201);

    case 'check_out':
        $d = readJsonBody();
        $stmt = getDB()->prepare("UPDATE visitor_logs SET check_out_time = NOW(), status = 'checked_out' WHERE log_id = ?");
        $stmt->execute([$d['log_id']]);
        logAudit('visitors', 'CHECK_OUT', 'visitor_logs', intval($d['log_id']), null, null);
        jsonResponse(['success' => true]);

    case 'preregister':
        $d = readJsonBody();
        $code = 'PR-' . date('Y') . '-' . str_pad(random_int(1,999999),6,'0',STR_PAD_LEFT);
        $stmt = getDB()->prepare("INSERT INTO visitor_preregistrations 
            (prereg_code, visitor_name, visitor_email, visitor_phone, visitor_company,
             host_user_id, purpose, expected_date, expected_time, status)
            VALUES (?,?,?,?,?,?,?,?,?,'pending')");
        $stmt->execute([
            $code, $d['visitor_name'], $d['visitor_email'] ?? null,
            $d['visitor_phone'] ?? null, $d['visitor_company'] ?? null,
            $d['host_user_id'], $d['purpose'], $d['expected_date'],
            $d['expected_time'] ?? null
        ]);
        logAudit('visitors', 'PREREGISTER', 'visitor_preregistrations', null, null, ['prereg_code' => $code, 'visitor_name' => $d['visitor_name']]);
        jsonResponse(['success' => true, 'prereg_code' => $code], 201);

    case 'lookup_visitor':
        $code = $_GET['code'] ?? '';
        if (!$code) jsonResponse(['error' => 'No code provided'], 400);
        // Try visitor_code first
        $stmt = getDB()->prepare("SELECT * FROM visitors WHERE visitor_code = ?");
        $stmt->execute([$code]);
        $visitor = $stmt->fetch();
        if ($visitor) {
            // Get active log
            $logStmt = getDB()->prepare("SELECT * FROM visitor_logs WHERE visitor_id = ? AND status = 'checked_in' ORDER BY check_in_time DESC LIMIT 1");
            $logStmt->execute([$visitor['visitor_id']]);
            $activeLog = $logStmt->fetch();
            jsonResponse(['found' => true, 'type' => 'visitor', 'visitor' => $visitor, 'active_log' => $activeLog ?: null]);
        }
        // Try prereg_code
        $stmt = getDB()->prepare("SELECT p.*, CONCAT(u.first_name,' ',u.last_name) AS host_name FROM visitor_preregistrations p JOIN users u ON p.host_user_id = u.user_id WHERE p.prereg_code = ?");
        $stmt->execute([$code]);
        $prereg = $stmt->fetch();
        if ($prereg) {
            jsonResponse(['found' => true, 'type' => 'prereg', 'prereg' => $prereg]);
        }
        // Try visit_code
        $stmt = getDB()->prepare("SELECT vl.*, CONCAT(v.first_name,' ',v.last_name) AS visitor_name, v.company, v.visitor_code FROM visitor_logs vl JOIN visitors v ON vl.visitor_id = v.visitor_id WHERE vl.visit_code = ?");
        $stmt->execute([$code]);
        $log = $stmt->fetch();
        if ($log) {
            jsonResponse(['found' => true, 'type' => 'visit_log', 'log' => $log]);
        }
        jsonResponse(['found' => false, 'error' => 'No visitor found with code: ' . $code], 404);

    case 'approve_prereg':
        $d = readJsonBody();
        $code = $d['prereg_code'] ?? '';
        if (!$code) jsonResponse(['error' => 'No prereg_code provided'], 400);
        // Fetch the pre-registration
        $stmt = getDB()->prepare("SELECT * FROM visitor_preregistrations WHERE prereg_code = ? AND status = 'pending'");
        $stmt->execute([$code]);
        $pr = $stmt->fetch();
        if (!$pr) jsonResponse(['error' => 'Pre-registration not found or already processed'], 404);
        // Update status to approved
        getDB()->prepare("UPDATE visitor_preregistrations SET status = 'approved' WHERE prereg_code = ?")->execute([$code]);
        // Auto-register as a visitor
        $nameParts = explode(' ', $pr['visitor_name'], 2);
        $firstName = $nameParts[0];
        $lastName  = $nameParts[1] ?? '';
        $visitorCode = 'VIS-' . str_pad(random_int(1,999999),6,'0',STR_PAD_LEFT);
        $ins = getDB()->prepare("INSERT INTO visitors (visitor_code, first_name, last_name, email, phone, company) VALUES (?,?,?,?,?,?)");
        $ins->execute([$visitorCode, $firstName, $lastName, $pr['visitor_email'], $pr['visitor_phone'], $pr['visitor_company']]);
        logAudit('visitors', 'APPROVE_PREREG', 'visitor_preregistrations', null, ['status' => 'pending'], ['status' => 'approved', 'visitor_code' => $visitorCode]);
        jsonResponse(['success' => true, 'visitor_code' => $visitorCode, 'message' => 'Pre-registration approved. Visitor registered as ' . $visitorCode]);

    case 'reject_prereg':
        $d = readJsonBody();
        $code = $d['prereg_code'] ?? '';
        if (!$code) jsonResponse(['error' => 'No prereg_code provided'], 400);
        $stmt = getDB()->prepare("UPDATE visitor_preregistrations SET status = 'rejected' WHERE prereg_code = ? AND status = 'pending'");
        $stmt->execute([$code]);
        if ($stmt->rowCount() === 0) jsonResponse(['error' => 'Pre-registration not found or already processed'], 404);
        logAudit('visitors', 'REJECT_PREREG', 'visitor_preregistrations', null, ['status' => 'pending'], ['status' => 'rejected']);
        jsonResponse(['success' => true]);

    case 'dashboard_stats':
        $db = getDB();
        $stats = [];
        $stats['total_visitors'] = $db->query("SELECT COUNT(*) FROM visitors")->fetchColumn();
        $stats['checked_in_now'] = $db->query("SELECT COUNT(*) FROM visitor_logs WHERE status='checked_in'")->fetchColumn();
        $stats['today_visits'] = $db->query("SELECT COUNT(*) FROM visitor_logs WHERE DATE(check_in_time)=CURDATE()")->fetchColumn();
        $stats['pending_preregs'] = $db->query("SELECT COUNT(*) FROM visitor_preregistrations WHERE status='pending'")->fetchColumn();
        $stats['blacklisted'] = $db->query("SELECT COUNT(*) FROM visitors WHERE is_blacklisted=1")->fetchColumn();
        $stats['total_visits_month'] = $db->query("SELECT COUNT(*) FROM visitor_logs WHERE MONTH(check_in_time)=MONTH(CURDATE()) AND YEAR(check_in_time)=YEAR(CURDATE())")->fetchColumn();
        $stats['avg_duration_min'] = $db->query("SELECT COALESCE(ROUND(AVG(TIMESTAMPDIFF(MINUTE, check_in_time, check_out_time)),0),0) FROM visitor_logs WHERE check_out_time IS NOT NULL AND MONTH(check_in_time)=MONTH(CURDATE())")->fetchColumn();
        $stats['top_company'] = $db->query("SELECT v.company FROM visitor_logs vl JOIN visitors v ON vl.visitor_id=v.visitor_id WHERE v.company IS NOT NULL GROUP BY v.company ORDER BY COUNT(*) DESC LIMIT 1")->fetchColumn() ?: 'N/A';
        jsonResponse($stats);

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
