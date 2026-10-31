
<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $error = 'Enter a name, valid email, and password with at least 8 characters.';
    } else {

        $stmt = $conn->prepare(
            'SELECT id FROM users WHERE email = ?'
        );

        $stmt->bind_param('s', $email);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {

            $error = 'An account with this email already exists.';

        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Generate a random six-digit demo OTP.
            $otp = (string) random_int(100000, 999999);

            $expires = date(
                'Y-m-d H:i:s',
                time() + OTP_EXPIRY_MINUTES * 60
            );

            $stmt = $conn->prepare(
                "INSERT INTO users
                (name, email, password, role, otp_code, otp_expires, verified)
                VALUES (?, ?, ?, 'student', ?, ?, 0)"
            );

            $stmt->bind_param(
                'sssss',
                $name,
                $email,
                $hash,
                $otp,
                $expires
            );

            if ($stmt->execute()) {

                $_SESSION['pending_email'] = $email;

                // No SMTP or email sending for demo verification.
                redirect('verify_otp.php');

            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$page_title = 'Create account';

include __DIR__ . '/includes/header.php';
?>

<section class="auth-wrap">

    <div class="auth-aside">

        <span class="eyebrow">START YOUR JOURNEY</span>

        <h1>
            Opportunity is closer than you think.
        </h1>

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
            </div>
        <?php endif; ?>

        <form method="post" class="stack-form">

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

            <label>
                Password

                <input
                    type="password"
                    id="registerPassword"
                    name="password"
                    minlength="8"
                    required
                    autocomplete="new-password"
                    style="
                        display: block;
                        width: 100%;
                        min-height: 45px;
                        background: white;
                        color: #172033;
                        position: relative;
                        z-index: 100;
                        pointer-events: auto;
                        cursor: text;
                    "
                >

                <small>At least 8 characters.</small>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
