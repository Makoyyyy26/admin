<?php
/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I
 * Audit Trail Helper
 * 
 * Usage:
 *   require_once __DIR__ . '/audit.php';
 *   logAudit('visitors', 'CREATE', 'visitors', $newId, null, $data);
 */

function logAudit(string $module, string $action, ?string $tableName = null, ?int $recordId = null, $oldValues = null, $newValues = null): void {
    try {
        $db = getDB(); // assumes config/db.php already loaded
        $userId = $_SESSION['user']['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $db->prepare(
            "INSERT INTO audit_logs (user_id, module, action, table_name, record_id, old_values, new_values, ip_address)
             VALUES (:uid, :mod, :act, :tbl, :rid, :old, :new, :ip)"
        );
        $stmt->execute([
            ':uid' => $userId,
            ':mod' => $module,
            ':act' => $action,
            ':tbl' => $tableName,
            ':rid' => $recordId,
            ':old' => $oldValues ? json_encode($oldValues) : null,
            ':new' => $newValues ? json_encode($newValues) : null,
            ':ip'  => $ip
        ]);
    } catch (Exception $e) {
        error_log('Audit log failed: ' . $e->getMessage());
    }
}
