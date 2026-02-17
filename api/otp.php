<?php
/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I: ADMINISTRATIVE
 * OTP API — Send, Verify, Resend one-time passwords via PHPMailer
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

require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ─── SEND OTP ───
    case 'send':
        // Must have passed credential check first
        if (empty($_SESSION['otp_pending'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Login credentials required first.']);
            exit;
        }

        $otp = generateOTP(OTP_LENGTH);
        $_SESSION['otp_code']    = $otp;
        $_SESSION['otp_created'] = time();

        $sent = sendOTPEmail($otp);

        if ($sent === true) {
            echo json_encode([
                'success'    => true,
                'message'    => 'OTP sent to your email.',
                'expires_in' => OTP_EXPIRY
            ]);
        } else {
            // Dev fallback: email failed, return OTP so user can still proceed
            echo json_encode([
                'success'      => true,
                'mail_failed'  => true,
                'fallback_otp' => $otp,
                'message'      => 'Email could not be sent (SMTP blocked). Use the code shown on screen.',
                'expires_in'   => OTP_EXPIRY
            ]);
        }
        break;

    // ─── VERIFY OTP ───
    case 'verify':
        if (empty($_SESSION['otp_pending'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No OTP session active.']);
            exit;
        }

        $input   = json_decode(file_get_contents('php://input'), true);
        $userOtp = trim($input['otp'] ?? '');

        if (empty($userOtp)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'OTP is required.']);
            exit;
        }

        // Check expiry
        $elapsed = time() - ($_SESSION['otp_created'] ?? 0);
        if ($elapsed > OTP_EXPIRY) {
            // Clear expired OTP
            unset($_SESSION['otp_code'], $_SESSION['otp_created']);
            http_response_code(410);
            echo json_encode([
                'success' => false,
                'expired' => true,
                'message' => 'OTP has expired. Please request a new one.'
            ]);
            exit;
        }

        // Check code
        if ($userOtp === $_SESSION['otp_code']) {
            // OTP verified — fully authenticate the session
            $_SESSION['authenticated'] = true;
            $_SESSION['user'] = $_SESSION['otp_user_data'];

            // Clean up OTP data
            unset(
                $_SESSION['otp_code'],
                $_SESSION['otp_created'],
                $_SESSION['otp_pending'],
                $_SESSION['otp_user_data']
            );

            echo json_encode([
                'success'  => true,
                'message'  => 'OTP verified. Redirecting to dashboard…',
                'redirect' => 'dashboard.php'
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.'
            ]);
        }
        break;

    // ─── RESEND OTP ───
    case 'resend':
        if (empty($_SESSION['otp_pending'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No OTP session active.']);
            exit;
        }

        $otp = generateOTP(OTP_LENGTH);
        $_SESSION['otp_code']    = $otp;
        $_SESSION['otp_created'] = time();

        $sent = sendOTPEmail($otp);

        if ($sent === true) {
            echo json_encode([
                'success'    => true,
                'message'    => 'New OTP sent to your email.',
                'expires_in' => OTP_EXPIRY
            ]);
        } else {
            // Dev fallback: email failed, return OTP
            echo json_encode([
                'success'      => true,
                'mail_failed'  => true,
                'fallback_otp' => $otp,
                'message'      => 'Email could not be sent. Use the code shown on screen.',
                'expires_in'   => OTP_EXPIRY
            ]);
        }
        break;

    // ─── TIME LEFT ───
    case 'time':
        if (empty($_SESSION['otp_pending']) || empty($_SESSION['otp_created'])) {
            echo json_encode(['remaining' => 0]);
            exit;
        }
        $elapsed   = time() - $_SESSION['otp_created'];
        $remaining = max(0, OTP_EXPIRY - $elapsed);
        echo json_encode(['remaining' => $remaining]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action. Use: send, verify, resend, time']);
}

// ─────────────── HELPERS ───────────────

/**
 * Generate a random numeric OTP of the given length.
 */
function generateOTP(int $length = 6): string {
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= random_int(0, 9);
    }
    return $otp;
}

/**
 * Send the OTP email using PHPMailer.
 * Returns true on success, or an error message string on failure.
 */
function sendOTPEmail(string $otp) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
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

        // Sender / Recipient
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress(OTP_RECIPIENT);

        // Embed the logo as an inline attachment
        $logoPath = __DIR__ . '/../assets/images/logo.png';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'company_logo', 'logo.png');
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Login OTP — Microfinancial Admin';
        $mail->Body    = getOTPEmailTemplate($otp);
        $mail->AltBody = "Your OTP code is: {$otp}\nThis code expires in " . OTP_EXPIRY . " seconds.\nDo not share this code with anyone.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

/**
 * Beautiful HTML email template for the OTP.
 */
function getOTPEmailTemplate(string $otp): string {
    $expiry = OTP_EXPIRY;
    $year   = date('Y');
    $userName = $_SESSION['otp_user_data']['first_name'] ?? 'User';

    $digits = str_split($otp);
    $digitBoxes = '';
    foreach ($digits as $d) {
        $digitBoxes .= "<td style=\"padding:0 5px;\"><div style=\"width:48px;height:56px;line-height:56px;text-align:center;font-size:30px;font-weight:700;color:#059669;background:#F0FDF4;border:2px solid #A7F3D0;border-radius:12px;font-family:'Segoe UI',Arial,sans-serif;\">{$d}</div></td>";
    }

    return "
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
}
