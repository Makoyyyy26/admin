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

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
