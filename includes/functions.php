
<?php

// =====================================
// SESSION
// =====================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================
// DATABASE CONNECTION
// =====================================

require_once __DIR__ . '/../config/db.php';


// =====================================
// ESCAPE OUTPUT SAFELY
// =====================================

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}


// =====================================
// REDIRECT
// =====================================

function redirect($path) {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}


// =====================================
// LOGIN CHECK
// =====================================

function is_logged_in() {
    return isset($_SESSION['user_id']);
}


// =====================================
// REQUIRE LOGIN
// =====================================

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}


// =====================================
// ADMIN CHECK
// =====================================

function is_admin() {

    $role = strtolower(trim((string)($_SESSION['role'] ?? '')));

    return is_logged_in() && $role === 'admin';
}


// =====================================
// REQUIRE ADMIN ACCESS
// =====================================

function require_admin() {

    require_login();

    if (!is_admin()) {
        http_response_code(403);
        exit('Access denied.');
    }
}


// =====================================
// FLASH MESSAGES
// =====================================

function flash($key, $message = null) {

    if ($message !== null) {

        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }

        $_SESSION['flash'][$key] = $message;

        return;
    }

    $value = $_SESSION['flash'][$key] ?? '';

    unset($_SESSION['flash'][$key]);

    return $value;
}


// =====================================
// SEND OTP EMAIL
// Kept for future real email verification
// =====================================

function send_otp_email($email, $name, $otp) {

    $autoload = __DIR__ . '/../vendor/autoload.php';

    if (!file_exists($autoload)) {
        return false;
    }

    require_once $autoload;

    try {

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;

        $mail->SMTPSecure =
            PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = SMTP_PORT;

        // Sender and receiver
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email, $name);

        // Email content
        $mail->isHTML(true);

        $mail->Subject = 'Your SkillBridge verification code';

        $mail->Body = '
            <div style="font-family:Arial;padding:24px">
                <h2>Welcome to SkillBridge</h2>

                <p>Your email verification code is:</p>

                <h1 style="letter-spacing:8px;color:#4f46e5">'
                    . e($otp) .
                '</h1>

                <p>This code expires in '
                    . (int)OTP_EXPIRY_MINUTES .
                ' minutes.</p>
            </div>
        ';

        $mail->AltBody =
            "Your SkillBridge verification code is $otp. " .
            "It expires in " . OTP_EXPIRY_MINUTES . " minutes.";

        $mail->send();

        return true;

    } catch (Throwable $e) {

        error_log('OTP mail error: ' . $e->getMessage());

        return false;
    }
}

?>

