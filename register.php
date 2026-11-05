
<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {

        $error = 'Invalid request. Please refresh and try again.';

    } elseif (
        $name === '' ||
        strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {

        $error = 'Enter a valid name and email. Password must contain at least 8 characters.';

    } elseif ($password !== $confirm_password) {

        $error = 'Password and confirm password do not match.';

    } else {

        try {

            $stmt = $conn->prepare(
                'SELECT id, verified FROM users WHERE email = ?'
            );

            $stmt->bind_param('s', $email);
            $stmt->execute();

            $existingUser = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($existingUser && (int)$existingUser['verified'] === 1) {

                $error = 'An account with this email already exists. Please sign in.';

            } else {

                // Generate secure six-digit OTP.
                $otp = (string) random_int(100000, 999999);

                $otpHash = password_hash($otp, PASSWORD_DEFAULT);
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                if ($existingUser) {

                    // Refresh existing unverified registration.
                    $stmt = $conn->prepare(
                        "UPDATE users
                         SET name = ?,
                             password = ?,
                             otp_code = ?,
                             otp_expires = DATE_ADD(NOW(), INTERVAL " .
                             (int)OTP_EXPIRY_MINUTES . " MINUTE)
                         WHERE id = ? AND verified = 0"
                    );

                    $stmt->bind_param(
                        'sssi',
                        $name,
                        $passwordHash,
                        $otpHash,
                        $existingUser['id']
                    );

                    $stmt->execute();
                    $stmt->close();

                } else {

                    // Create new unverified student account.
                    $stmt = $conn->prepare(
                        "INSERT INTO users
                         (name, email, password, role, verified, otp_code, otp_expires)
                         VALUES (?, ?, ?, 'student', 0, ?,
                         DATE_ADD(NOW(), INTERVAL " .
                         (int)OTP_EXPIRY_MINUTES . " MINUTE))"
                    );

                    $stmt->bind_param(
                        'ssss',
                        $name,
                        $email,
                        $passwordHash,
                        $otpHash
                    );

                    $stmt->execute();
                    $stmt->close();
                }

                $_SESSION['pending_email'] = $email;

                // Send OTP through Gmail.
                if (send_otp_email($email, $name, $otp)) {

                    flash(
                        'success',
                        'A verification code has been sent to your email.'
                    );

                    redirect('verify_otp.php');

                } else {

                    $error = 'Your account is saved, but the OTP email could not be sent. Please check your email settings and try again from the verification page.';
                }
            }

        } catch (Throwable $e) {

            error_log('SkillBridge registration error: ' . $e->getMessage());

            $error = 'Registration could not be completed. Please try again.';
        }
    }
}

$page_title = 'Create account';

include __DIR__ . '/includes/header.php';
?>

<style>
.password-wrapper {
    position: relative;
    width: 100%;
}

.password-wrapper input {
    width: 100%;
    padding-right: 48px;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 19px;
    padding: 5px;
    color: #172554;
}

.password-toggle:focus-visible {
    outline: 2px solid #2563eb;
    border-radius: 5px;
}
</style>

<section class="auth-wrap">

    <div class="auth-aside">

        <span class="eyebrow">START YOUR JOURNEY</span>

        <h1>Opportunity is closer than you think.</h1>

        <p>
            Create your account and discover roles designed to help you grow.
        </p>

        <div class="aside-stat">
            <b>One profile.</b>
            <span>More possibilities.</span>
        </div>

    </div>

    <div class="form-card">

        <h2>Create account</h2>

        <p class="muted">
            Join the SkillBridge community.
        </p>

        <?php if ($error): ?>
            <div class="alert error">
                <?= e($error) ?>

                <?php if (!empty($_SESSION['pending_email'])): ?>
                    <p>
                        <a href="<?= BASE_URL ?>/verify_otp.php">
                            Continue to email verification
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="stack-form">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >

            <label>
                Full name

                <input
                    type="text"
                    name="name"
                    required
                    maxlength="100"
                    autocomplete="name"
                    value="<?= e($_POST['name'] ?? '') ?>"
                >
            </label>

            <label>
                Email address

                <input
                    type="email"
                    name="email"
                    required
                    autocomplete="email"
                    value="<?= e($_POST['email'] ?? '') ?>"
                >
            </label>

            <!-- Password -->
            <label>
                Password

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        required
                        autocomplete="new-password"
                        placeholder="Enter password"
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('password', this)"
                        aria-label="Show password"
                        title="Show password"
                    >
                        👁
                    </button>

                </div>

                <small>At least 8 characters.</small>
            </label>

            <!-- Confirm Password -->
            <label>
                Confirm password

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        minlength="8"
                        required
                        autocomplete="new-password"
                        placeholder="Re-enter password"
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('confirm_password', this)"
                        aria-label="Show confirm password"
                        title="Show confirm password"
                    >
                        👁
                    </button>

                </div>
            </label>

            <button type="submit" class="btn primary full">
                Create account
            </button>

        </form>

        <p class="form-foot">
            Already registered?
            <a href="login.php">Sign in</a>
        </p>

    </div>

</section>

<script>
function togglePassword(inputId, button) {

    const input = document.getElementById(inputId);

    if (input.type === 'password') {

        input.type = 'text';
        button.textContent = '🙈';
        button.setAttribute('aria-label', 'Hide password');
        button.setAttribute('title', 'Hide password');

    } else {

        input.type = 'password';
        button.textContent = '👁';
        button.setAttribute('aria-label', 'Show password');
        button.setAttribute('title', 'Show password');
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
