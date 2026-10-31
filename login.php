
<?php

require_once __DIR__ . '/includes/functions.php';

// Redirect already logged-in users based on their role
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin.php');
    } else {
        redirect('dashboard.php');
    }
}

$error = '';

// =====================================
// HANDLE LOGIN
// =====================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } else {

        $stmt = $conn->prepare("
            SELECT id, name, email, password, role, verified
            FROM users
            WHERE email = ?
        ");

        $stmt->bind_param('s', $email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$user || !password_verify($password, $user['password'])) {

            $error = 'Incorrect email or password.';

        } elseif (!(int)$user['verified']) {

            $_SESSION['pending_email'] = $email;

            $error = 'Please verify your email before signing in.';

        } else {

            // Regenerate session ID after successful login
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            unset($_SESSION['pending_email']);

            // Redirect according to account role
            if (strtolower(trim($user['role'])) === 'admin') {
                redirect('admin.php');
            } else {
                redirect('dashboard.php');
            }
        }
    }
}

// =====================================
// PAGE DISPLAY
// =====================================

$page_title = 'Sign in';

include __DIR__ . '/includes/header.php';

?>

<section class="auth-wrap">

    <div class="auth-aside">

        <span class="eyebrow">
            WELCOME BACK
        </span>

        <h1>
            Your next opportunity is waiting.
        </h1>

        <p>
            Sign in to continue exploring and managing your applications.
        </p>

        <div class="aside-stat">
            <b>Discover.</b>
            <span>Apply. Achieve.</span>
        </div>

    </div>

    <div class="form-card">

        <h2>Welcome back</h2>

        <p class="muted">
            Sign in to your account.
        </p>

        <?php if ($error): ?>

            <div class="alert error">

                <?= e($error) ?>

                <?php if (str_contains($error, 'verify your email')): ?>

                    <a href="<?= BASE_URL ?>/verify_otp.php">
                        Verify now
                    </a>

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <form method="post" class="stack-form">

            <label>
                Email address

                <input
                    type="email"
                    name="email"
                    value="<?= e($_POST['email'] ?? '') ?>"
                    placeholder="Enter your email"
                    required
                >
            </label>

            <label>
                Password

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >
            </label>

            <button type="submit" class="btn primary full">
                Sign in
            </button>

        </form>

        <p class="form-foot">
            New to SkillBridge?

            <a href="register.php">
                Create account
            </a>
        </p>

    </div>

</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
