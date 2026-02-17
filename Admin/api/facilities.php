<?php
/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I
 * API: Facilities Reservation Module
 * 
 * Endpoints (via ?action=...):
 *   GET  list_facilities       – All facilities
 *   GET  list_reservations     – Reservations (filterable)
 *   GET  list_equipment        – Equipment inventory
 *   GET  list_maintenance      – Maintenance tickets
 *   GET  dashboard_stats       – Summary counts
 *   POST create_reservation    – New reservation
 *   POST update_status         – Approve/reject/cancel
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

    /* ───── Facilities ───── */
    case 'list_facilities':
        $rows = getDB()->query("SELECT * FROM facilities ORDER BY name")->fetchAll();
        jsonResponse(['data' => $rows]);

    /* ───── Reservations ───── */
    case 'list_reservations':
        $status = $_GET['status'] ?? null;
        $type   = $_GET['type'] ?? null;
        $date   = $_GET['date'] ?? null;
        $sql = "SELECT r.*, f.name AS facility_name, f.type AS facility_type, f.capacity,
                       CONCAT(u.first_name,' ',u.last_name) AS reserved_by_name
                FROM facility_reservations r
                JOIN facilities f ON r.facility_id = f.facility_id
                JOIN users u ON r.reserved_by = u.user_id WHERE 1=1";
        $params = [];
        if ($status) { $sql .= " AND r.status = ?"; $params[] = $status; }
        if ($type)   { $sql .= " AND r.reservation_type = ?"; $params[] = $type; }
        if ($date)   { $sql .= " AND DATE(r.start_datetime) = ?"; $params[] = $date; }
        $sql .= " ORDER BY r.start_datetime DESC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'calendar_events':
        $month = $_GET['month'] ?? date('m');
        $year  = $_GET['year']  ?? date('Y');
        $sql = "SELECT r.reservation_id, r.reservation_code, r.event_title, r.purpose,
                       r.start_datetime, r.end_datetime, r.status, r.reservation_type,
                       r.priority, r.budget, r.department,
                       f.name AS facility_name, f.type AS facility_type
                FROM facility_reservations r
                JOIN facilities f ON r.facility_id = f.facility_id
                WHERE MONTH(r.start_datetime) = ? AND YEAR(r.start_datetime) = ?
                  AND r.status NOT IN ('cancelled','rejected')
                ORDER BY r.start_datetime ASC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute([$month, $year]);
        jsonResponse(['data' => $stmt->fetchAll()]);

    case 'room_status':
        // Auto-sync facility statuses based on current reservations
        // 1) Set occupied if there is an active approved reservation NOW
        getDB()->exec("UPDATE facilities f SET f.status = 'occupied'
            WHERE f.status != 'maintenance' AND f.status != 'retired'
            AND EXISTS (
                SELECT 1 FROM facility_reservations r 
                WHERE r.facility_id = f.facility_id 
                  AND r.status = 'approved' 
                  AND NOW() BETWEEN r.start_datetime AND r.end_datetime
            )");
        // 2) Set available if no active approved reservation is happening NOW
        getDB()->exec("UPDATE facilities f SET f.status = 'available'
            WHERE f.status = 'occupied'
            AND NOT EXISTS (
                SELECT 1 FROM facility_reservations r 
                WHERE r.facility_id = f.facility_id 
                  AND r.status = 'approved' 
                  AND NOW() BETWEEN r.start_datetime AND r.end_datetime
            )");
        // 3) Auto-complete reservations whose end_datetime has passed
        getDB()->exec("UPDATE facility_reservations SET status = 'completed'
            WHERE status = 'approved' AND end_datetime < NOW()");

        $sql = "SELECT f.*,
                  (SELECT COUNT(*) FROM facility_reservations r 
                   WHERE r.facility_id = f.facility_id 
                     AND r.status = 'approved' 
                     AND NOW() BETWEEN r.start_datetime AND r.end_datetime) AS is_currently_occupied,
                  (SELECT r.event_title FROM facility_reservations r 
                   WHERE r.facility_id = f.facility_id 
                     AND r.status = 'approved' 
                     AND NOW() BETWEEN r.start_datetime AND r.end_datetime 
                   LIMIT 1) AS current_event,
                  (SELECT r.end_datetime FROM facility_reservations r
                   WHERE r.facility_id = f.facility_id
                     AND r.status = 'approved'
                     AND NOW() BETWEEN r.start_datetime AND r.end_datetime
                   LIMIT 1) AS occupied_until,
                  (SELECT COUNT(*) FROM facility_reservations r 
                   WHERE r.facility_id = f.facility_id 
                     AND r.status = 'approved' 
                     AND DATE(r.start_datetime) = CURDATE()) AS today_bookings,
                  (SELECT COUNT(*) FROM facility_reservations r 
                   WHERE r.facility_id = f.facility_id 
                     AND r.status = 'approved' 
                     AND r.start_datetime > NOW()) AS has_upcoming
                FROM facilities f ORDER BY f.name";
        $rows = getDB()->query($sql)->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'create_reservation':
        $d = readJsonBody();
        $stmt = getDB()->prepare("INSERT INTO facility_reservations 
            (reservation_code, facility_id, reserved_by, department, purpose, event_title,
             reservation_type, priority, start_datetime, end_datetime, attendees_count,
             budget, equipment_needed, special_requests, status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $code = 'RES-' . date('Y') . '-' . str_pad(random_int(1,999999),6,'0',STR_PAD_LEFT);
        $autoApprove = ($d['reservation_type'] ?? 'regular') === 'emergency' ? 'approved' : 'pending';
        $stmt->execute([
            $code, $d['facility_id'], $d['reserved_by'] ?? 1, $d['department'] ?? null,
            $d['purpose'], $d['event_title'] ?? null,
            $d['reservation_type'] ?? 'regular', $d['priority'] ?? 'normal',
            $d['start_datetime'], $d['end_datetime'],
            $d['attendees_count'] ?? null, $d['budget'] ?? 0,
            json_encode($d['equipment_needed'] ?? []),
            $d['special_requests'] ?? null, $autoApprove
        ]);
        $resId = getDB()->lastInsertId();
        // Link equipment if provided
        if (!empty($d['equipment_needed']) && is_array($d['equipment_needed'])) {
            $eqStmt = getDB()->prepare("INSERT IGNORE INTO reservation_equipment 
                (reservation_id, equipment_id, quantity) VALUES (?,?,1)");
            foreach ($d['equipment_needed'] as $eqId) {
                if (is_numeric($eqId)) $eqStmt->execute([$resId, $eqId]);
            }
        }
        logAudit('facilities', 'CREATE_RESERVATION', 'facility_reservations', $resId, null, ['reservation_code' => $code, 'facility_id' => $d['facility_id']]);
        jsonResponse(['success' => true, 'reservation_code' => $code, 'reservation_id' => $resId], 201);

    case 'validate_reservation':
        $d = readJsonBody();
        $userId = $_SESSION['user']['user_id'] ?? ($d['validated_by'] ?? 1);
        if (!is_numeric($userId)) $userId = 1;
        $stmt = getDB()->prepare("UPDATE facility_reservations 
            SET is_validated = 1, validated_by = ?, validated_at = NOW(), remarks = CONCAT(IFNULL(remarks,''), '\n[Validated]')
            WHERE reservation_id = ?");
        $stmt->execute([intval($userId), $d['reservation_id']]);
        logAudit('facilities', 'VALIDATE_RESERVATION', 'facility_reservations', intval($d['reservation_id']), null, ['validated' => true]);
        jsonResponse(['success' => true]);

    case 'update_status':
        $d = readJsonBody();
        $userId = $_SESSION['user']['user_id'] ?? ($d['approved_by'] ?? 1);
        if (!is_numeric($userId)) $userId = 1;
        $stmt = getDB()->prepare("UPDATE facility_reservations SET status = ?, remarks = ?, 
            approved_by = ?, approved_at = NOW() WHERE reservation_id = ?");
        $stmt->execute([$d['status'], $d['remarks'] ?? null, intval($userId), $d['reservation_id']]);

        // When approved, auto-set facility to 'occupied' if reservation is currently active
        if (($d['status'] ?? '') === 'approved') {
            $resRow = getDB()->prepare("SELECT facility_id, start_datetime, end_datetime FROM facility_reservations WHERE reservation_id = ?");
            $resRow->execute([$d['reservation_id']]);
            $resData = $resRow->fetch();
            if ($resData) {
                $now = date('Y-m-d H:i:s');
                if ($now >= $resData['start_datetime'] && $now <= $resData['end_datetime']) {
                    getDB()->prepare("UPDATE facilities SET status = 'occupied' WHERE facility_id = ?")->execute([$resData['facility_id']]);
                }
            }
        }

        logAudit('facilities', 'UPDATE_RESERVATION_STATUS', 'facility_reservations', intval($d['reservation_id']), null, ['status' => $d['status']]);
        jsonResponse(['success' => true]);

    case 'complete_reservation':
        $d = readJsonBody();
        $userId = $_SESSION['user']['user_id'] ?? 1;
        if (!is_numeric($userId)) $userId = 1;
        $resId = intval($d['reservation_id'] ?? 0);

        // Get facility_id before updating
        $resRow = getDB()->prepare("SELECT facility_id FROM facility_reservations WHERE reservation_id = ?");
        $resRow->execute([$resId]);
        $resData = $resRow->fetch();

        // Mark reservation as completed
        $stmt = getDB()->prepare("UPDATE facility_reservations SET status = 'completed', remarks = CONCAT(IFNULL(remarks,''), '\n[Completed]') WHERE reservation_id = ? AND status = 'approved'");
        $stmt->execute([$resId]);

        // Set facility back to available if no other active approved reservations
        if ($resData) {
            $activeCheck = getDB()->prepare("SELECT COUNT(*) FROM facility_reservations WHERE facility_id = ? AND status = 'approved' AND NOW() BETWEEN start_datetime AND end_datetime AND reservation_id != ?");
            $activeCheck->execute([$resData['facility_id'], $resId]);
            if ($activeCheck->fetchColumn() == 0) {
                getDB()->prepare("UPDATE facilities SET status = 'available' WHERE facility_id = ?")->execute([$resData['facility_id']]);
            }
        }

        logAudit('facilities', 'COMPLETE_RESERVATION', 'facility_reservations', $resId, null, ['completed' => true]);
        jsonResponse(['success' => true]);

    /* ───── Equipment ───── */
    case 'list_equipment':
        $rows = getDB()->query("SELECT e.*, f.name AS facility_name 
            FROM facility_equipment e 
            LEFT JOIN facilities f ON e.facility_id = f.facility_id
            ORDER BY e.name")->fetchAll();
        jsonResponse(['data' => $rows]);

    /* ───── Maintenance ───── */
    case 'list_maintenance':
        $rows = getDB()->query("SELECT m.*, f.name AS facility_name,
            CONCAT(u.first_name,' ',u.last_name) AS reported_by_name,
            e.name AS equipment_name
            FROM facility_maintenance m
            JOIN facilities f ON m.facility_id = f.facility_id
            JOIN users u ON m.reported_by = u.user_id
            LEFT JOIN facility_equipment e ON m.equipment_id = e.equipment_id
            ORDER BY
              CASE m.status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 WHEN 'resolved' THEN 2 WHEN 'closed' THEN 3 END,
              CASE m.priority WHEN 'critical' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END,
              m.created_at DESC")->fetchAll();
        jsonResponse(['data' => $rows]);

    case 'create_maintenance':
        $d = readJsonBody();
        $ticket = 'MNT-' . date('Y') . '-' . str_pad(random_int(1,999999),6,'0',STR_PAD_LEFT);
        $userId = $_SESSION['user']['user_id'] ?? ($d['reported_by'] ?? 1);
        if (!is_numeric($userId)) $userId = 1;

        $stmt = getDB()->prepare("INSERT INTO facility_maintenance
            (ticket_number, facility_id, equipment_id, reported_by, issue_type, priority, description, status, assigned_to)
            VALUES (?,?,?,?,?,?,?,'open',?)");
        $equipId = !empty($d['equipment_id']) && is_numeric($d['equipment_id']) ? intval($d['equipment_id']) : null;
        $stmt->execute([
            $ticket,
            intval($d['facility_id']),
            $equipId,
            intval($userId),
            $d['issue_type'] ?? 'equipment',
            $d['priority'] ?? 'medium',
            $d['description'] ?? '',
            $d['assigned_to'] ?? null
        ]);
        $maintId = getDB()->lastInsertId();

        // If equipment malfunction, mark equipment condition as needs_repair
        if ($equipId) {
            getDB()->prepare("UPDATE facility_equipment SET condition_status = 'needs_repair', is_available = 0 WHERE equipment_id = ?")->execute([$equipId]);
        }

        // If critical/high priority, optionally set facility to maintenance status
        if (in_array($d['priority'] ?? '', ['critical', 'high']) && !empty($d['set_facility_maintenance'])) {
            getDB()->prepare("UPDATE facilities SET status = 'maintenance' WHERE facility_id = ?")->execute([intval($d['facility_id'])]);
        }

        logAudit('facilities', 'CREATE_MAINTENANCE', 'facility_maintenance', $maintId, null, [
            'ticket_number' => $ticket, 'facility_id' => $d['facility_id'], 'equipment_id' => $equipId, 'issue_type' => $d['issue_type'] ?? 'equipment'
        ]);
        jsonResponse(['success' => true, 'ticket_number' => $ticket, 'maintenance_id' => $maintId], 201);

    case 'update_maintenance':
        $d = readJsonBody();
        $maintId = intval($d['maintenance_id'] ?? 0);
        $newStatus = $d['status'] ?? '';
        $resolution = $d['resolution_notes'] ?? null;
        $assignedTo = $d['assigned_to'] ?? null;

        // Get current maintenance record
        $cur = getDB()->prepare("SELECT * FROM facility_maintenance WHERE maintenance_id = ?");
        $cur->execute([$maintId]);
        $curData = $cur->fetch();
        if (!$curData) {
            jsonResponse(['error' => 'Maintenance record not found'], 404);
            break;
        }

        $updates = [];
        $params = [];
        if ($newStatus) {
            $updates[] = "status = ?";
            $params[] = $newStatus;
            if ($newStatus === 'resolved' || $newStatus === 'closed') {
                $updates[] = "resolved_at = NOW()";
            }
        }
        if ($resolution !== null) {
            $updates[] = "resolution_notes = ?";
            $params[] = $resolution;
        }
        if ($assignedTo !== null) {
            $updates[] = "assigned_to = ?";
            $params[] = $assignedTo;
        }

        if ($updates) {
            $params[] = $maintId;
            $sql = "UPDATE facility_maintenance SET " . implode(', ', $updates) . " WHERE maintenance_id = ?";
            getDB()->prepare($sql)->execute($params);
        }

        // When resolved/closed, restore equipment and facility status
        if (in_array($newStatus, ['resolved', 'closed'])) {
            if ($curData['equipment_id']) {
                getDB()->prepare("UPDATE facility_equipment SET condition_status = 'good', is_available = 1 WHERE equipment_id = ?")->execute([$curData['equipment_id']]);
            }
            // Check if facility has any other open maintenance tickets
            $openCheck = getDB()->prepare("SELECT COUNT(*) FROM facility_maintenance WHERE facility_id = ? AND status IN ('open','in_progress') AND maintenance_id != ?");
            $openCheck->execute([$curData['facility_id'], $maintId]);
            if ($openCheck->fetchColumn() == 0) {
                getDB()->prepare("UPDATE facilities SET status = 'available' WHERE facility_id = ? AND status = 'maintenance'")->execute([$curData['facility_id']]);
            }
        }

        logAudit('facilities', 'UPDATE_MAINTENANCE', 'facility_maintenance', $maintId, null, ['status' => $newStatus]);
        jsonResponse(['success' => true]);

    /* ───── Dashboard Stats ───── */
    case 'dashboard_stats':
        $db = getDB();
        $stats = [];
        $stats['total_facilities'] = $db->query("SELECT COUNT(*) FROM facilities")->fetchColumn();
        $stats['available_facilities'] = $db->query("SELECT COUNT(*) FROM facilities WHERE status='available'")->fetchColumn();
        $stats['pending_reservations'] = $db->query("SELECT COUNT(*) FROM facility_reservations WHERE status='pending'")->fetchColumn();
        $stats['today_reservations'] = $db->query("SELECT COUNT(*) FROM facility_reservations WHERE DATE(start_datetime)=CURDATE()")->fetchColumn();
        $stats['total_equipment'] = $db->query("SELECT COUNT(*) FROM facility_equipment")->fetchColumn();
        $stats['open_maintenance'] = $db->query("SELECT COUNT(*) FROM facility_maintenance WHERE status IN ('open','in_progress')")->fetchColumn();
        jsonResponse($stats);

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
