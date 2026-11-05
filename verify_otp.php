
<?php
require_once __DIR__ . '/includes/functions.php';

// Get pending email from registration session.
$email = $_SESSION['pending_email'] ?? '';

if (!$email) {
    redirect('register.php');
}

// CSRF protection.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

$error = '';
$success_message = '';

// Get unverified user's details.
$stmt = $conn->prepare(
    'SELECT id, name, email, otp_code, otp_expires,
            (otp_expires IS NOT NULL AND otp_expires > NOW()) AS otp_is_valid
     FROM users
     WHERE email = ? AND verified = 0'
);

$stmt->bind_param('s', $email);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    unset($_SESSION['pending_email']);
    redirect('login.php');
}

// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submitted_token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submitted_token) ||
        !hash_equals($csrf_token, $submitted_token)
    ) {
        $error = 'Invalid request. Please refresh the page and try again.';
    }

    // RESEND OTP
    elseif (isset($_POST['resend'])) {

        $new_otp = (string) random_int(100000, 999999);
        $hashed_otp = password_hash($new_otp, PASSWORD_DEFAULT);

        $expiry_minutes = max(1, (int) OTP_EXPIRY_MINUTES);

        $stmt = $conn->prepare(
            "UPDATE users
             SET otp_code = ?,
                 otp_expires = DATE_ADD(NOW(), INTERVAL {$expiry_minutes} MINUTE)
             WHERE id = ? AND verified = 0"
        );

        $stmt->bind_param('si', $hashed_otp, $user['id']);

        if ($stmt->execute() && $stmt->affected_rows > 0) {

            if (send_otp_email($user['email'], $user['name'], $new_otp)) {
                $success_message = 'A new verification code has been sent to your email address.';
            } else {
                $error = 'Unable to send the verification email. Please try again.';
            }

        } else {
            $error = 'Unable to generate a new OTP. Please try again.';
        }
    }

    // VERIFY OTP
    else {

        $entered_otp = trim($_POST['otp'] ?? '');

        if (!preg_match('/^[0-9]{6}$/', $entered_otp)) {

            $error = 'Please enter a valid six-digit OTP.';

        } elseif (
            empty($user['otp_code']) ||
            (int) $user['otp_is_valid'] !== 1
        ) {

            $error = 'Your OTP has expired. Please request a new one.';

        } elseif (!password_verify($entered_otp, $user['otp_code'])) {

            $error = 'Incorrect OTP. Please check your email and try again.';

        } else {

            $stmt = $conn->prepare(
                'UPDATE users
                 SET verified = 1,
                     otp_code = NULL,
                     otp_expires = NULL
                 WHERE id = ?
                   AND verified = 0
                   AND otp_expires > NOW()'
            );

            $stmt->bind_param('i', $user['id']);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {

                unset($_SESSION['pending_email']);

                flash(
                    'success',
                    'Email verified successfully! You can now log in.'
                );

                redirect('login.php');

            } else {
                $error = 'Your OTP has expired or verification failed. Please request a new OTP.';
            }
        }
    }
}

$page_title = 'Verify email';

include __DIR__ . '/includes/header.php';
?>

<div class="center-page">

    <div class="form-card narrow">

        <div class="big-icon">✉</div>

        <h2>Verify your email</h2>

        <p class="muted">
            We have sent a verification code to
            <b><?= e($email) ?></b>
        </p>

        <p class="muted">
            Please check your inbox and spam folder.
            Your OTP is valid for <?= (int) OTP_EXPIRY_MINUTES ?> minutes.
        </p>

        <?php if ($success_message): ?>
            <div class="alert success">
                <?= e($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <!-- OTP verification form -->
        <form method="post" class="stack-form" id="verifyForm">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($csrf_token) ?>"
            >

            <label>
                Verification code

                <input
                    type="text"
                    name="otp"
                    id="otpInput"
                    inputmode="numeric"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    required
                    placeholder="Enter six-digit OTP"
                    autocomplete="one-time-code"
                >
            </label>

            <!-- Auto-fill OTP button -->
            <button
                type="button"
                class="btn full"
                id="autofillOtp"
                style="background:#e9e8ff;color:#5546e8;border:1px solid #5546e8;"
            >
                📋 Auto-fill OTP
            </button>

            <p id="autofillMessage"
               class="muted"
               style="font-size:13px;text-align:center;"
               aria-live="polite">
            </p>

            <button
                type="submit"
                class="btn primary full"
            >
                Verify email
            </button>

        </form>

        <!-- Resend OTP form -->
        <form method="post" style="margin-top:15px;">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($csrf_token) ?>"
            >

            <button
                type="submit"
                name="resend"
                value="1"
                class="link-button"
            >
                Resend verification code
            </button>

        </form>

    </div>

</div>

<script>
document.getElementById('autofillOtp').addEventListener('click', async function () {

    const otpInput = document.getElementById('otpInput');
    const message = document.getElementById('autofillMessage');

    try {

        const clipboardText = await navigator.clipboard.readText();

        const otp = clipboardText.trim().match(/\b\d{6}\b/);

        if (otp) {

            otpInput.value = otp[0];
            otpInput.focus();

            message.textContent = 'OTP filled successfully!';
            message.style.color = 'green';

        } else {

            message.textContent = 'No six-digit OTP found. Copy your OTP from Gmail first.';
            message.style.color = 'red';
        }

    } catch (error) {

        message.textContent = 'Clipboard access blocked. Please paste your OTP manually.';
        message.style.color = 'red';
    }

});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
