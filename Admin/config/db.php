<?php
/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I: ADMINISTRATIVE
 * Database Configuration - localhost:3306
 * 
 * Connects to the `administrative` database used by all 4 modules:
 *  - Facilities Reservation
 *  - Document Management (Archiving)
 *  - Legal Management
 *  - Visitor Management
 */

define('DB_HOST',    'localhost');
define('DB_PORT',    3306);
define('DB_NAME',    'administrative');
define('DB_USER',    'root');
define('DB_PASS',    '');          // default XAMPP - change in production
define('DB_CHARSET', 'utf8mb4');

/**
 * Get a PDO connection to the administrative database.
 * Uses a singleton pattern so only one connection is created per request.
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

/**
 * Send a JSON response and terminate.
 */
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Read JSON body from a POST/PUT request.
 */
function readJsonBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
//