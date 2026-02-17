<?php
/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I
 * API: Document Management (Archiving) Module
 * 
 * Endpoints (via ?action=...):
 *   GET  list_documents      – All documents (filterable)
 *   GET  list_categories     – Document categories
 *   GET  list_versions       – Version history for a document
 *   GET  list_ocr_queue      – OCR processing queue
 *   GET  dashboard_stats     – Summary counts
 *   POST create_document     – Upload/register new document
 *   POST update_document     – Update document metadata
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

    case 'list_documents':
        $cat = $_GET['category_id'] ?? null;
        $status = $_GET['status'] ?? null;
        $sql = "SELECT d.*, c.name AS category_name,
                       CONCAT(u.first_name,' ',u.last_name) AS uploaded_by_name
                FROM documents d
                LEFT JOIN document_categories c ON d.category_id = c.category_id
                JOIN users u ON d.uploaded_by = u.user_id WHERE 1=1";
        $params = [];
        if ($cat) { $sql .= " AND d.category_id = ?"; $params[] = $cat; }
        if ($status) { $sql .= " AND d.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY d.created_at DESC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'list_categories':
        $rows = getDB()->query("SELECT * FROM document_categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'list_versions':
        $docId = $_GET['document_id'] ?? 0;
        $stmt = getDB()->prepare("SELECT v.*, CONCAT(u.first_name,' ',u.last_name) AS uploaded_by_name
            FROM document_versions v JOIN users u ON v.uploaded_by = u.user_id
            WHERE v.document_id = ? ORDER BY v.version_number DESC");
        $stmt->execute([$docId]);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'list_ocr_queue':
        $rows = getDB()->query("SELECT q.*, d.title AS document_title, d.document_code
            FROM ocr_queue q JOIN documents d ON q.document_id = d.document_id
            ORDER BY q.created_at DESC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'create_document':
        $d = readJsonBody();
        $code = 'DOC-' . date('Y') . '-' . str_pad(random_int(1,999999),6,'0',STR_PAD_LEFT);
        $stmt = getDB()->prepare("INSERT INTO documents 
            (document_code, title, category_id, document_type, description, file_path, file_name,
             file_size, file_type, uploaded_by, department, confidentiality, status, ocr_status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $code, $d['title'], $d['category_id'] ?? null, $d['document_type'] ?? 'other',
            $d['description'] ?? null, $d['file_path'] ?? '/uploads/documents/', $d['file_name'] ?? 'unknown',
            $d['file_size'] ?? 0, $d['file_type'] ?? 'application/pdf',
            $d['uploaded_by'] ?? 1, $d['department'] ?? null,
            $d['confidentiality'] ?? 'internal', $d['status'] ?? 'active',
            $d['ocr_status'] ?? 'pending'
        ]);
        logAudit('documents', 'CREATE_DOCUMENT', 'documents', null, null, ['document_code' => $code, 'title' => $d['title']]);
        jsonResponse(['success' => true, 'document_code' => $code], 201);

    case 'update_document':
        $d = readJsonBody();
        $stmt = getDB()->prepare("UPDATE documents SET title=?, category_id=?, description=?,
            confidentiality=?, status=? WHERE document_id=?");
        $stmt->execute([$d['title'], $d['category_id'], $d['description'] ?? null,
            $d['confidentiality'] ?? 'internal', $d['status'] ?? 'active', $d['document_id']]);
        logAudit('documents', 'UPDATE_DOCUMENT', 'documents', intval($d['document_id']), null, ['title' => $d['title']]);
        jsonResponse(['success' => true]);

    case 'dashboard_stats':
        $db = getDB();
        $stats = [];
        $stats['total_documents'] = $db->query("SELECT COUNT(*) FROM documents")->fetchColumn();
        $stats['active_documents'] = $db->query("SELECT COUNT(*) FROM documents WHERE created_at > DATE_SUB(NOW(), INTERVAL 6 MONTH)")->fetchColumn();
        $stats['archived_documents'] = $db->query("SELECT COUNT(*) FROM documents WHERE created_at <= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND created_at > DATE_SUB(NOW(), INTERVAL 3 YEAR)")->fetchColumn();
        $stats['retained_documents'] = $db->query("SELECT COUNT(*) FROM documents WHERE created_at <= DATE_SUB(NOW(), INTERVAL 3 YEAR)")->fetchColumn();
        $stats['departments'] = $db->query("SELECT COUNT(DISTINCT department) FROM documents WHERE department IS NOT NULL")->fetchColumn();
        $stats['pending_ocr'] = $db->query("SELECT COUNT(*) FROM documents WHERE ocr_status IN ('pending','processing')")->fetchColumn();
        jsonResponse($stats);

    case 'list_by_department':
        $dept = $_GET['department'] ?? null;
        $folder = $_GET['folder'] ?? null;
        $sql = "SELECT d.*, c.name AS category_name FROM documents d
                LEFT JOIN document_categories c ON d.category_id = c.category_id WHERE 1=1";
        $params = [];
        if ($dept) { $sql .= " AND d.department = ?"; $params[] = $dept; }
        if ($folder) { $sql .= " AND d.folder_name = ?"; $params[] = $folder; }
        $sql .= " ORDER BY d.created_at DESC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'list_folders':
        $rows = getDB()->query("SELECT department, folder_name,
            COUNT(*) as doc_count,
            SUM(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 6 MONTH) THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN created_at <= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND created_at > DATE_SUB(NOW(), INTERVAL 3 YEAR) THEN 1 ELSE 0 END) as archived_count,
            SUM(CASE WHEN created_at <= DATE_SUB(NOW(), INTERVAL 3 YEAR) THEN 1 ELSE 0 END) as retained_count
            FROM documents GROUP BY department, folder_name ORDER BY department")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'search_documents':
        $q = $_GET['q'] ?? '';
        $stmt = getDB()->prepare("SELECT d.*, c.name AS category_name FROM documents d
            LEFT JOIN document_categories c ON d.category_id = c.category_id
            WHERE (d.title LIKE ? OR d.description LIKE ? OR d.folder_name LIKE ? OR d.department LIKE ?)
            ORDER BY d.created_at DESC");
        $like = "%{$q}%";
        $stmt->execute([$like, $like, $like, $like]);
        jsonResponse(['data' => $stmt->fetchAll()]);

    // ═══════════════════════════════════════════
    // SECURE STORAGE
    // ═══════════════════════════════════════════

    case 'secure_storage_stats':
        $db = getDB();
        $stats = [];
        $stats['total_files'] = $db->query("SELECT COUNT(*) FROM documents")->fetchColumn();
        $stats['total_size'] = $db->query("SELECT COALESCE(SUM(file_size),0) FROM documents")->fetchColumn();
        $stats['public'] = $db->query("SELECT COUNT(*) FROM documents WHERE confidentiality='public'")->fetchColumn();
        $stats['internal'] = $db->query("SELECT COUNT(*) FROM documents WHERE confidentiality='internal'")->fetchColumn();
        $stats['confidential'] = $db->query("SELECT COUNT(*) FROM documents WHERE confidentiality='confidential'")->fetchColumn();
        $stats['restricted'] = $db->query("SELECT COUNT(*) FROM documents WHERE confidentiality='restricted'")->fetchColumn();
        $stats['encrypted'] = $db->query("SELECT COUNT(*) FROM documents WHERE confidentiality IN ('confidential','restricted')")->fetchColumn();
        jsonResponse(['data' => $stats]);

    case 'list_secure_documents':
        $conf = $_GET['confidentiality'] ?? null;
        $sql = "SELECT d.document_id, d.document_code, d.title, d.file_name, d.file_size, d.file_type,
                       d.department, d.confidentiality, d.status, d.created_at,
                       CONCAT(u.first_name,' ',u.last_name) AS uploaded_by_name,
                       (SELECT COUNT(*) FROM document_access da WHERE da.document_id = d.document_id) AS access_count
                FROM documents d
                JOIN users u ON d.uploaded_by = u.user_id WHERE 1=1";
        $params = [];
        if ($conf) { $sql .= " AND d.confidentiality = ?"; $params[] = $conf; }
        $sql .= " ORDER BY FIELD(d.confidentiality,'restricted','confidential','internal','public'), d.created_at DESC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'update_confidentiality':
        $d = readJsonBody();
        $stmt = getDB()->prepare("UPDATE documents SET confidentiality=? WHERE document_id=?");
        $stmt->execute([$d['confidentiality'], $d['document_id']]);
        logAudit('documents', 'UPDATE_CONFIDENTIALITY', 'documents', intval($d['document_id']), null, ['confidentiality' => $d['confidentiality']]);
        jsonResponse(['success' => true]);

    // ═══════════════════════════════════════════
    // OCR SCANNING
    // ═══════════════════════════════════════════

    case 'ocr_stats':
        $db = getDB();
        $stats = [];
        $stats['total_scanned'] = $db->query("SELECT COUNT(*) FROM documents WHERE ocr_status='completed'")->fetchColumn();
        $stats['pending'] = $db->query("SELECT COUNT(*) FROM documents WHERE ocr_status='pending'")->fetchColumn();
        $stats['processing'] = $db->query("SELECT COUNT(*) FROM documents WHERE ocr_status='processing'")->fetchColumn();
        $stats['failed'] = $db->query("SELECT COUNT(*) FROM documents WHERE ocr_status='failed'")->fetchColumn();
        $stats['queue_count'] = $db->query("SELECT COUNT(*) FROM ocr_queue WHERE status IN ('queued','processing')")->fetchColumn();
        jsonResponse(['data' => $stats]);

    case 'list_ocr_documents':
        $ocrStatus = $_GET['ocr_status'] ?? null;
        $sql = "SELECT d.document_id, d.document_code, d.title, d.file_name, d.file_type, d.file_size,
                       d.department, d.ocr_status, d.ocr_processed_at, d.ocr_text, d.created_at,
                       CONCAT(u.first_name,' ',u.last_name) AS uploaded_by_name
                FROM documents d
                JOIN users u ON d.uploaded_by = u.user_id WHERE d.ocr_status != 'not_applicable'";
        $params = [];
        if ($ocrStatus) { $sql .= " AND d.ocr_status = ?"; $params[] = $ocrStatus; }
        $sql .= " ORDER BY FIELD(d.ocr_status,'processing','pending','failed','completed'), d.created_at DESC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'queue_ocr':
        $d = readJsonBody();
        $db = getDB();
        // Update document OCR status
        $stmt = $db->prepare("UPDATE documents SET ocr_status='pending' WHERE document_id=? AND ocr_status IN ('not_applicable','failed')");
        $stmt->execute([$d['document_id']]);
        // Insert into queue
        $stmt = $db->prepare("INSERT INTO ocr_queue (document_id, priority, status) VALUES (?, ?, 'queued')
            ON DUPLICATE KEY UPDATE status='queued', attempts=0, error_message=NULL");
        $stmt->execute([$d['document_id'], $d['priority'] ?? 'normal']);
        logAudit('documents', 'QUEUE_OCR', 'ocr_queue', intval($d['document_id']), null, ['priority' => $d['priority'] ?? 'normal']);
        jsonResponse(['success' => true]);

    case 'view_ocr_text':
        $docId = $_GET['document_id'] ?? 0;
        $stmt = getDB()->prepare("SELECT document_id, document_code, title, ocr_text, ocr_status, ocr_processed_at FROM documents WHERE document_id=?");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch();
        jsonResponse(['data' => $doc ?: null]);

    // ═══════════════════════════════════════════
    // VERSION CONTROL
    // ═══════════════════════════════════════════

    case 'version_stats':
        $db = getDB();
        $stats = [];
        $stats['total_versions'] = $db->query("SELECT COUNT(*) FROM document_versions")->fetchColumn();
        $stats['documents_with_versions'] = $db->query("SELECT COUNT(DISTINCT document_id) FROM document_versions")->fetchColumn();
        $stats['latest_version_date'] = $db->query("SELECT MAX(created_at) FROM document_versions")->fetchColumn();
        $stats['avg_versions'] = $db->query("SELECT ROUND(AVG(cnt),1) FROM (SELECT COUNT(*) as cnt FROM document_versions GROUP BY document_id) t")->fetchColumn();
        jsonResponse(['data' => $stats]);

    case 'list_versioned_documents':
        $sql = "SELECT d.document_id, d.document_code, d.title, d.version as current_version,
                       d.department, d.file_name, d.file_type, d.created_at, d.updated_at,
                       CONCAT(u.first_name,' ',u.last_name) AS uploaded_by_name,
                       (SELECT COUNT(*) FROM document_versions v WHERE v.document_id = d.document_id) AS version_count,
                       (SELECT MAX(v.created_at) FROM document_versions v WHERE v.document_id = d.document_id) AS last_version_date
                FROM documents d
                JOIN users u ON d.uploaded_by = u.user_id
                ORDER BY d.updated_at DESC";
        $rows = getDB()->query($sql)->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'create_version':
        $d = readJsonBody();
        $db = getDB();
        // Get next version number
        $stmt = $db->prepare("SELECT COALESCE(MAX(version_number),0)+1 FROM document_versions WHERE document_id=?");
        $stmt->execute([$d['document_id']]);
        $nextVer = $stmt->fetchColumn();
        // Insert version record
        $stmt = $db->prepare("INSERT INTO document_versions (document_id, version_number, file_path, file_name, file_size, change_notes, uploaded_by) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $d['document_id'], $nextVer,
            $d['file_path'] ?? '/uploads/documents/',
            $d['file_name'] ?? 'unknown',
            $d['file_size'] ?? 0,
            $d['change_notes'] ?? null,
            $_SESSION['user']['user_id'] ?? 1
        ]);
        // Update main doc version
        $stmt = $db->prepare("UPDATE documents SET version=? WHERE document_id=?");
        $stmt->execute([$nextVer, $d['document_id']]);
        logAudit('documents', 'CREATE_VERSION', 'document_versions', intval($d['document_id']), null, ['version' => $nextVer, 'notes' => $d['change_notes'] ?? '']);
        jsonResponse(['success' => true, 'version_number' => $nextVer], 201);

    // ═══════════════════════════════════════════
    // ARCHIVING
    // ═══════════════════════════════════════════

    case 'archiving_stats':
        $db = getDB();
        $stats = [];
        $stats['total_active'] = $db->query("SELECT COUNT(*) FROM documents WHERE created_at > DATE_SUB(NOW(), INTERVAL 6 MONTH)")->fetchColumn();
        $stats['total_archived'] = $db->query("SELECT COUNT(*) FROM documents WHERE created_at <= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND created_at > DATE_SUB(NOW(), INTERVAL 3 YEAR)")->fetchColumn();
        $stats['total_retained'] = $db->query("SELECT COUNT(*) FROM documents WHERE created_at <= DATE_SUB(NOW(), INTERVAL 3 YEAR)")->fetchColumn();
        $stats['pending_archive'] = $db->query("SELECT COUNT(*) FROM documents WHERE status='active' AND created_at <= DATE_SUB(NOW(), INTERVAL 6 MONTH)")->fetchColumn();
        $stats['auto_archived_today'] = $db->query("SELECT COUNT(*) FROM documents WHERE archived_at IS NOT NULL AND DATE(archived_at) = CURDATE()")->fetchColumn();
        $stats['oldest_document'] = $db->query("SELECT MIN(created_at) FROM documents")->fetchColumn();
        jsonResponse(['data' => $stats]);

    case 'list_archive_timeline':
        $sql = "SELECT 
                  DATE_FORMAT(created_at, '%Y-%m') AS month,
                  COUNT(*) AS total,
                  SUM(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 6 MONTH) THEN 1 ELSE 0 END) AS active,
                  SUM(CASE WHEN created_at <= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND created_at > DATE_SUB(NOW(), INTERVAL 3 YEAR) THEN 1 ELSE 0 END) AS archived,
                  SUM(CASE WHEN created_at <= DATE_SUB(NOW(), INTERVAL 3 YEAR) THEN 1 ELSE 0 END) AS retained
                FROM documents 
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC LIMIT 24";
        $rows = getDB()->query($sql)->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'run_archive_cycle':
        $db = getDB();
        // Auto-archive documents older than 6 months that are still active
        $stmt = $db->prepare("UPDATE documents SET status='archived', archived_at=NOW() WHERE status='active' AND created_at <= DATE_SUB(NOW(), INTERVAL 6 MONTH)");
        $stmt->execute();
        $archivedCount = $stmt->rowCount();
        // Auto-retain documents older than 3 years
        $stmt = $db->prepare("UPDATE documents SET status='retained', retained_at=NOW() WHERE status='archived' AND created_at <= DATE_SUB(NOW(), INTERVAL 3 YEAR)");
        $stmt->execute();
        $retainedCount = $stmt->rowCount();
        logAudit('documents', 'RUN_ARCHIVE_CYCLE', 'documents', null, null, ['archived' => $archivedCount, 'retained' => $retainedCount]);
        jsonResponse(['success' => true, 'archived' => $archivedCount, 'retained' => $retainedCount]);

    // ═══════════════════════════════════════════
    // ACCESS CONTROL
    // ═══════════════════════════════════════════

    case 'access_control_stats':
        $db = getDB();
        $stats = [];
        $stats['total_grants'] = $db->query("SELECT COUNT(*) FROM document_access")->fetchColumn();
        $stats['active_grants'] = $db->query("SELECT COUNT(*) FROM document_access WHERE expires_at IS NULL OR expires_at > NOW()")->fetchColumn();
        $stats['expired_grants'] = $db->query("SELECT COUNT(*) FROM document_access WHERE expires_at IS NOT NULL AND expires_at <= NOW()")->fetchColumn();
        $stats['view_only'] = $db->query("SELECT COUNT(*) FROM document_access WHERE permission='view'")->fetchColumn();
        $stats['download'] = $db->query("SELECT COUNT(*) FROM document_access WHERE permission='download'")->fetchColumn();
        $stats['edit'] = $db->query("SELECT COUNT(*) FROM document_access WHERE permission='edit'")->fetchColumn();
        $stats['admin'] = $db->query("SELECT COUNT(*) FROM document_access WHERE permission='admin'")->fetchColumn();
        $stats['users_with_access'] = $db->query("SELECT COUNT(DISTINCT user_id) FROM document_access")->fetchColumn();
        jsonResponse(['data' => $stats]);

    case 'list_access_grants':
        $docId = $_GET['document_id'] ?? null;
        $userId = $_GET['user_id'] ?? null;
        $sql = "SELECT da.access_id, da.document_id, da.user_id, da.permission, da.expires_at, da.created_at,
                       d.document_code, d.title AS document_title, d.confidentiality,
                       CONCAT(u.first_name,' ',u.last_name) AS user_name, u.department AS user_department, u.role AS user_role,
                       CONCAT(g.first_name,' ',g.last_name) AS granted_by_name
                FROM document_access da
                JOIN documents d ON da.document_id = d.document_id
                JOIN users u ON da.user_id = u.user_id
                LEFT JOIN users g ON da.granted_by = g.user_id
                WHERE 1=1";
        $params = [];
        if ($docId) { $sql .= " AND da.document_id = ?"; $params[] = $docId; }
        if ($userId) { $sql .= " AND da.user_id = ?"; $params[] = $userId; }
        $sql .= " ORDER BY da.created_at DESC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'grant_access':
        $d = readJsonBody();
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO document_access (document_id, user_id, permission, granted_by, expires_at) 
            VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE permission=VALUES(permission), granted_by=VALUES(granted_by), expires_at=VALUES(expires_at)");
        $stmt->execute([
            $d['document_id'], $d['user_id'],
            $d['permission'] ?? 'view',
            $_SESSION['user']['user_id'] ?? null,
            $d['expires_at'] ?? null
        ]);
        logAudit('documents', 'GRANT_ACCESS', 'document_access', intval($d['document_id']), null, [
            'user_id' => $d['user_id'], 'permission' => $d['permission'] ?? 'view'
        ]);
        jsonResponse(['success' => true], 201);

    case 'revoke_access':
        $d = readJsonBody();
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM document_access WHERE access_id=?");
        $stmt->execute([$d['access_id']]);
        logAudit('documents', 'REVOKE_ACCESS', 'document_access', intval($d['access_id']), null, null);
        jsonResponse(['success' => true]);

    case 'list_users':
        $rows = getDB()->query("SELECT user_id, employee_id, first_name, last_name, email, role, department FROM users WHERE is_active=1 ORDER BY first_name")->fetchAll();
        jsonResponse(['data' => $rows]);

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
