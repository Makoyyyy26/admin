<?php
/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I: ADMINISTRATIVE
 * Authentication API — Database-backed login with OTP verification
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include DB, mail config & PHPMailer for OTP
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/audit.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ─── LOGIN ───
    case 'login':
        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        // Look up user by employee_id OR email
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE (employee_id = :u OR email = :u2) AND is_active = 1 LIMIT 1");
        $stmt->execute([':u' => $username, ':u2' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Don't fully authenticate yet — require OTP first
            $_SESSION['otp_pending'] = true;
            $_SESSION['otp_user_data'] = [
                'user_id'    => $user['user_id'],
                'employee_id'=> $user['employee_id'],
                'username'   => $user['employee_id'],
                'name'       => $user['first_name'] . ' ' . $user['last_name'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'email'      => $user['email'],
                'role'       => $user['role'],
                'department' => $user['department'],
                'avatar_url' => $user['avatar_url'],
                'login_time' => date('Y-m-d H:i:s')
            ];

            // Update last_login
            $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id")->execute([':id' => $user['user_id']]);
            logAudit('system', 'LOGIN', 'users', $user['user_id'], null, ['employee_id' => $user['employee_id']]);

            // OTP will be generated and sent from the OTP page
            // This keeps login fast — no SMTP wait here
            echo json_encode([
                'success'      => true,
                'otp_required' => true,
                'message'      => 'Credentials verified. Redirecting to OTP verification…'
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password'
            ]);
        }
        break;

    // ─── LOGOUT ───
    case 'logout':
        $logUserId = $_SESSION['user']['user_id'] ?? null;
        if ($logUserId) logAudit('system', 'LOGOUT', 'users', $logUserId, null, null);
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out']);
        break;

    // ─── CHECK SESSION ───
    case 'check':
        if (!empty($_SESSION['authenticated'])) {
            echo json_encode([
                'authenticated' => true,
                'user'          => $_SESSION['user']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['authenticated' => false]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action. Use: login, logout, check']);
}

/**
 * Send the login OTP email via PHPMailer.
 * Returns true on success, or error message string on failure.
 */
function sendLoginOTP(string $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->Timeout    = 10;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress(OTP_RECIPIENT);

        // Embed the logo as an inline attachment
        $logoPath = __DIR__ . '/../assets/images/logo.png';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'company_logo', 'logo.png');
        }

        $mail->isHTML(true);
        $mail->Subject = 'Your Login OTP — Microfinancial Admin';

        $digits = str_split($otp);
        $digitBoxes = '';
        foreach ($digits as $d) {
            $digitBoxes .= "<td style=\"padding:0 5px;\"><div style=\"width:48px;height:56px;line-height:56px;text-align:center;font-size:30px;font-weight:700;color:#059669;background:#F0FDF4;border:2px solid #A7F3D0;border-radius:12px;font-family:'Segoe UI',Arial,sans-serif;\">{$d}</div></td>";
        }
        $expiry = OTP_EXPIRY;
        $year   = date('Y');
        $userName = $_SESSION['otp_user_data']['first_name'] ?? 'User';

        $mail->Body = "
        <div style=\"background-color:#f3f4f6;padding:40px 20px;font-family:'Segoe UI',Roboto,Arial,sans-serif;\">
          <div style=\"max-width:520px;margin:0 auto;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.10);\">

            <!-- Header with Logo -->
            <div style=\"background:linear-gradient(135deg,#059669 0%,#047857 50%,#065f46 100%);padding:36px 24px 28px;text-align:center;\">
              <img src=\"cid:company_logo\" alt=\"Microfinancial\" style=\"width:72px;height:72px;margin:0 auto 12px;display:block;border-radius:50%;background:#fff;padding:4px;box-shadow:0 4px 12px rgba(0,0,0,0.15);\" />
              <h1 style=\"margin:0;color:#ffffff;font-size:24px;font-weight:700;letter-spacing:0.5px;\">Microfinancial Admin</h1>
              <p style=\"margin:6px 0 0;color:rgba(255,255,255,0.80);font-size:13px;letter-spacing:0.3px;\">Management System I &mdash; Administrative</p>
            </div>

            <!-- Body -->
            <div style=\"padding:36px 32px 28px;text-align:center;\">
              <div style=\"width:56px;height:56px;margin:0 auto 16px;background:#F0FDF4;border-radius:50%;text-align:center;line-height:56px;\">
                <span style=\"font-size:28px;\">&#128274;</span>
              </div>
              <h2 style=\"margin:0 0 8px;color:#1F2937;font-size:22px;font-weight:700;\">Login Verification</h2>
              <p style=\"color:#6B7280;font-size:15px;margin:0 0 28px;line-height:1.6;\">
                Hi <strong style=\"color:#1F2937;\">{$userName}</strong>, use the code below to complete your sign-in.
              </p>

              <!-- OTP Code -->
              <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"margin:0 auto 28px;\">
                <tr>{$digitBoxes}</tr>
              </table>

              <!-- Warning Box -->
              <div style=\"background:linear-gradient(135deg,#FFFBEB,#FEF3C7);border:1px solid #FDE68A;border-radius:12px;padding:14px 20px;margin:0 0 24px;text-align:left;\">
                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
                  <tr>
                    <td style=\"width:28px;vertical-align:top;padding-top:2px;\"><span style=\"font-size:18px;\">&#9200;</span></td>
                    <td style=\"color:#92400E;font-size:13px;line-height:1.5;\">
                      This code expires in <strong>{$expiry} seconds</strong>.<br/>
                      Do not share this code with anyone.
                    </td>
                  </tr>
                </table>
              </div>

              <!-- Security Note -->
              <div style=\"border-top:1px solid #E5E7EB;padding-top:20px;\">
                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
                  <tr>
                    <td style=\"width:28px;vertical-align:top;padding-top:2px;\"><span style=\"font-size:16px;\">&#128737;</span></td>
                    <td style=\"color:#9CA3AF;font-size:12px;line-height:1.5;\">
                      If you did not attempt to log in, please ignore this email or contact your system administrator immediately.
                    </td>
                  </tr>
                </table>
              </div>
            </div>

            <!-- Footer -->
            <div style=\"background:#F9FAFB;padding:20px 24px;text-align:center;border-top:1px solid #E5E7EB;\">
              <p style=\"margin:0 0 4px;color:#6B7280;font-size:12px;font-weight:600;\">Microfinancial Management System</p>
              <p style=\"margin:0;color:#9CA3AF;font-size:11px;\">&copy; {$year} All Rights Reserved &mdash; This is an automated message.</p>
            </div>

          </div>
        </div>";

        $mail->AltBody = "Hi {$userName},\n\nYour OTP code is: {$otp}\nThis code expires in {$expiry} seconds.\nDo not share this code with anyone.\n\nIf you did not attempt to log in, please ignore this email.\n\n© {$year} Microfinancial Management System";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
