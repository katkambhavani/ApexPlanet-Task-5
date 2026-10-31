
<?php
require_once __DIR__ . '/includes/functions.php';

$email = $_SESSION['pending_email'] ?? '';

if (!$email) {
    redirect('register.php');
}

$error = '';
$success_message = '';
$otp = '';

// Get the current OTP from the database.
$stmt = $conn->prepare(
    'SELECT id, otp_code, otp_expires
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

$otp = $user['otp_code'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['resend'])) {

        // Generate a new random OTP.
        $otp = (string) random_int(100000, 999999);

        $expiry = date(
            'Y-m-d H:i:s',
            time() + OTP_EXPIRY_MINUTES * 60
        );

        $stmt = $conn->prepare(
            'UPDATE users
             SET otp_code = ?, otp_expires = ?
             WHERE id = ? AND verified = 0'
        );

        $stmt->bind_param(
            'ssi',
            $otp,
            $expiry,
            $user['id']
        );

        $stmt->execute();

        $success_message = 'A new demo OTP has been generated.';

    } else {

        $entered_otp = trim($_POST['otp'] ?? '');

        if (
            $entered_otp === $otp &&
            !empty($user['otp_expires']) &&
            strtotime($user['otp_expires']) >= time()
        ) {

            $stmt = $conn->prepare(
                'UPDATE users
                 SET verified = 1,
                     otp_code = NULL,
                     otp_expires = NULL
                 WHERE id = ? AND verified = 0'
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
                $error = 'Verification failed. Please try again.';
            }

        } else {
            $error = 'Incorrect or expired OTP. Please try again.';
        }
    }
}

$page_title = 'Verify email';

include __DIR__ . '/includes/header.php';
?>

<!-- Top notification -->
<div
    id="otpNotification"
    style="
        position: fixed;
        top: 85px;
        right: 20px;
        width: min(380px, calc(100% - 40px));
        padding: 18px;
        background: #ffffff;
        color: #172554;
        border-left: 5px solid #2563eb;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.18);
        z-index: 99999;
    "
>
    <div style="font-weight:700; margin-bottom:8px;">
        🔔 SkillBridge Notification
    </div>

    <div>
        Your verification OTP is:
        <strong style="font-size:20px;">
            <?= e($otp) ?>
        </strong>
    </div>

    <small style="display:block; margin-top:8px;">
        Valid for 10 minutes.
    </small>
</div>

<div class="center-page">
    <div class="form-card narrow">

        <div class="big-icon">✉</div>

        <h2>Verify your email</h2>

        <p class="muted">
            Verification for
            <b><?= e($email) ?></b>
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

        <form method="post" class="stack-form">

            <label>
                Verification code

                <input
                    type="text"
                    id="otpInput"
                    name="otp"
                    inputmode="numeric"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    required
                    placeholder="Enter six-digit OTP"
                    autocomplete="one-time-code"
                >
            </label>

            <button
                type="button"
                id="autoFillOtp"
                class="btn secondary full"
            >
                ✨ Auto-fill OTP
            </button>

            <button
                type="submit"
                class="btn primary full"
            >
                Verify email
            </button>

        </form>

        <form method="post" style="margin-top:15px;">
            <button
                type="submit"
                name="resend"
                value="1"
                class="link-button"
            >
                Generate a new OTP
            </button>
        </form>

    </div>
</div>

<script>
document.getElementById('autoFillOtp').addEventListener('click', function () {

    const otp = <?= json_encode($otp) ?>;
    const input = document.getElementById('otpInput');

    input.value = otp;
    input.focus();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
