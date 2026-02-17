<?php
/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I: ADMINISTRATIVE
 * Mail Configuration — Gmail SMTP via PHPMailer
 *
 * Uses a Gmail App Password for authentication.
 * Generate one at: Google Account > Security > 2-Step Verification > App Passwords
 */

define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_USERNAME',   'imjesselobina@gmail.com');
define('MAIL_PASSWORD',   'xclw aauv fgki nsaa');
define('MAIL_FROM_EMAIL', 'imjesselobina@gmail.com');
define('MAIL_FROM_NAME',  'Microfinancial Admin System');

// OTP Settings
define('OTP_LENGTH',      6);          // 6-digit OTP
define('OTP_EXPIRY',      60);         // 60 seconds = 1 minute
define('OTP_RECIPIENT',   'imjesselobina@gmail.com');
