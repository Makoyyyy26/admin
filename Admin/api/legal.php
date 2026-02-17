<?php
/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I
 * API: Legal Management Module
 * 
 * Endpoints (via ?action=...):
 *   GET  list_cases          – Legal cases
 *   GET  list_contracts      – Contracts & agreements
 *   GET  list_compliance     – Compliance tracking
 *   GET  list_loans          – Loan documentation
 *   GET  list_collaterals    – Collateral registry
 *   GET  list_demands        – Demand letters
 *   GET  list_kyc            – KYC records
 *   GET  list_resolutions    – Board resolutions
 *   GET  list_poa            – Power of Attorney
 *   GET  list_permits        – Permits & licenses
 *   GET  dashboard_stats     – Summary counts
 *   POST create_case         – New legal case
 *   POST create_contract     – New contract
 *   POST update_case_status  – Update case status
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

    case 'list_cases':
        $status = $_GET['status'] ?? null;
        $sql = "SELECT lc.*, CONCAT(u.first_name,' ',u.last_name) AS assigned_name,
                       CONCAT(cr.first_name,' ',cr.last_name) AS created_by_name
                FROM legal_cases lc
                LEFT JOIN users u ON lc.assigned_to = u.user_id
                JOIN users cr ON lc.created_by = cr.user_id";
        $params = [];
        if ($status) { $sql .= " WHERE lc.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY lc.created_at DESC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'list_contracts':
        $rows = getDB()->query("SELECT c.*, CONCAT(u.first_name,' ',u.last_name) AS assigned_name
            FROM legal_contracts c
            LEFT JOIN users u ON c.assigned_to = u.user_id
            ORDER BY c.created_at DESC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'list_compliance':
        $rows = getDB()->query("SELECT c.*, CONCAT(u.first_name,' ',u.last_name) AS assigned_name
            FROM legal_compliance c
            LEFT JOIN users u ON c.assigned_to = u.user_id
            ORDER BY c.deadline ASC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'list_loans':
        $status = $_GET['status'] ?? null;
        $sql = "SELECT l.*, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
                FROM loan_documentation l
                JOIN users u ON l.created_by = u.user_id WHERE 1=1";
        $params = [];
        if ($status) { $sql .= " AND l.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY l.created_at DESC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'list_collaterals':
        $rows = getDB()->query("SELECT c.*, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
            FROM collateral_registry c
            JOIN users u ON c.created_by = u.user_id
            ORDER BY c.created_at DESC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'list_demands':
        $rows = getDB()->query("SELECT d.*, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
            FROM demand_letters d
            JOIN users u ON d.created_by = u.user_id
            ORDER BY d.created_at DESC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'list_kyc':
        $rows = getDB()->query("SELECT k.*, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
            FROM kyc_records k
            JOIN users u ON k.created_by = u.user_id
            ORDER BY k.created_at DESC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'list_resolutions':
        $rows = getDB()->query("SELECT r.*, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
            FROM board_resolutions r
            JOIN users u ON r.created_by = u.user_id
            ORDER BY r.meeting_date DESC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'list_poa':
        $rows = getDB()->query("SELECT p.*, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
            FROM power_of_attorney p
            JOIN users u ON p.created_by = u.user_id
            ORDER BY p.created_at DESC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'list_permits':
        $rows = getDB()->query("SELECT p.*, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
            FROM permits_licenses p
            JOIN users u ON p.created_by = u.user_id
            ORDER BY p.expiry_date ASC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'create_case':
        $d = readJsonBody();
        $num = 'LC-' . date('Y') . '-' . str_pad(random_int(1,999999),6,'0',STR_PAD_LEFT);
        $stmt = getDB()->prepare("INSERT INTO legal_cases 
            (case_number, title, case_type, description, priority, status, filing_date, due_date,
             opposing_party, court_venue, assigned_lawyer, assigned_to, department, financial_impact, created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $num, $d['title'], $d['case_type'], $d['description'],
            $d['priority'] ?? 'medium', 'open', $d['filing_date'] ?? date('Y-m-d'),
            $d['due_date'] ?? null, $d['opposing_party'] ?? null, $d['court_venue'] ?? null,
            $d['assigned_lawyer'] ?? null, $d['assigned_to'] ?? null,
            $d['department'] ?? null, $d['financial_impact'] ?? 0, $d['created_by'] ?? 1
        ]);
        logAudit('legal', 'CREATE_CASE', 'legal_cases', null, null, ['case_number' => $num, 'title' => $d['title']]);
        jsonResponse(['success' => true, 'case_number' => $num], 201);

    case 'create_contract':
        $d = readJsonBody();
        $num = 'CON-' . date('Y') . '-' . str_pad(random_int(1,999999),6,'0',STR_PAD_LEFT);
        $stmt = getDB()->prepare("INSERT INTO legal_contracts
            (contract_number, title, contract_type, party_name, description,
             start_date, end_date, value, status, assigned_to, created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $num, $d['title'], $d['contract_type'], $d['party_name'],
            $d['description'] ?? null, $d['start_date'], $d['end_date'] ?? null,
            $d['value'] ?? 0, 'draft', $d['assigned_to'] ?? null, $d['created_by'] ?? 1
        ]);
        logAudit('legal', 'CREATE_CONTRACT', 'legal_contracts', null, null, ['contract_number' => $num, 'title' => $d['title']]);
        jsonResponse(['success' => true, 'contract_number' => $num], 201);

    case 'update_case_status':
        $d = readJsonBody();
        $stmt = getDB()->prepare("UPDATE legal_cases SET status=?, resolution_summary=? WHERE case_id=?");
        $stmt->execute([$d['status'], $d['resolution_summary'] ?? null, $d['case_id']]);
        logAudit('legal', 'UPDATE_CASE_STATUS', 'legal_cases', intval($d['case_id']), null, ['status' => $d['status']]);
        jsonResponse(['success' => true]);

    case 'dashboard_stats':
        $db = getDB();
        $stats = [];
        $stats['total_loans'] = $db->query("SELECT COUNT(*) FROM loan_documentation")->fetchColumn();
        $stats['active_loans'] = $db->query("SELECT COUNT(*) FROM loan_documentation WHERE status IN ('active','signed')")->fetchColumn();
        $stats['total_collaterals'] = $db->query("SELECT COUNT(*) FROM collateral_registry")->fetchColumn();
        $stats['active_collaterals'] = $db->query("SELECT COUNT(*) FROM collateral_registry WHERE lien_status='active'")->fetchColumn();
        $stats['total_cases'] = $db->query("SELECT COUNT(*) FROM legal_cases")->fetchColumn();
        $stats['active_cases'] = $db->query("SELECT COUNT(*) FROM legal_cases WHERE status IN ('open','in_progress')")->fetchColumn();
        $stats['total_demands'] = $db->query("SELECT COUNT(*) FROM demand_letters")->fetchColumn();
        $stats['total_kyc'] = $db->query("SELECT COUNT(*) FROM kyc_records")->fetchColumn();
        $stats['verified_kyc'] = $db->query("SELECT COUNT(*) FROM kyc_records WHERE verification_status='verified'")->fetchColumn();
        $stats['total_contracts'] = $db->query("SELECT COUNT(*) FROM legal_contracts")->fetchColumn();
        $stats['active_contracts'] = $db->query("SELECT COUNT(*) FROM legal_contracts WHERE status='active'")->fetchColumn();
        $stats['compliance_items'] = $db->query("SELECT COUNT(*) FROM legal_compliance")->fetchColumn();
        $stats['non_compliant'] = $db->query("SELECT COUNT(*) FROM legal_compliance WHERE status='non_compliant'")->fetchColumn();
        $stats['total_resolutions'] = $db->query("SELECT COUNT(*) FROM board_resolutions")->fetchColumn();
        $stats['total_poa'] = $db->query("SELECT COUNT(*) FROM power_of_attorney")->fetchColumn();
        $stats['active_poa'] = $db->query("SELECT COUNT(*) FROM power_of_attorney WHERE status='active'")->fetchColumn();
        $stats['total_permits'] = $db->query("SELECT COUNT(*) FROM permits_licenses")->fetchColumn();
        $stats['active_permits'] = $db->query("SELECT COUNT(*) FROM permits_licenses WHERE status='active'")->fetchColumn();
        $stats['expiring_permits'] = $db->query("SELECT COUNT(*) FROM permits_licenses WHERE status='active' AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)")->fetchColumn();
        $stats['total_financial_exposure'] = $db->query("SELECT COALESCE(SUM(financial_impact),0) FROM legal_cases WHERE status IN ('open','in_progress')")->fetchColumn();
        jsonResponse($stats);

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
