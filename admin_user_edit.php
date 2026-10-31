
<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    flash('error', 'Invalid user selected.');
    redirect('admin_users.php');
}

$error = '';

$stmt = $conn->prepare("
    SELECT id, name, email, role, verified
    FROM users
    WHERE id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    flash('error', 'User not found.');
    redirect('admin_users.php');
}

$is_current_user = ((int)$user['id'] === (int)$_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');

    if ($name === '' || $email === '') {
        $error = 'Please complete all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($role, ['student', 'admin'], true)) {
        $error = 'Please select a valid role.';
    } elseif ($is_current_user && $role !== 'admin') {
        $error = 'You cannot remove your own Admin role.';
    } else {
        try {
            $stmt = $conn->prepare("
                UPDATE users
                SET name = ?, email = ?, role = ?
                WHERE id = ?
            ");

            $stmt->bind_param('sssi', $name, $email, $role, $id);
            $stmt->execute();
            $stmt->close();

            // Keep the current session updated if editing your own account.
            if ($is_current_user) {
                $_SESSION['name'] = $name;
                $_SESSION['role'] = $role;
            }

            flash('success', 'User details updated successfully.');
            redirect('admin_users.php');

        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $error = 'This email address is already registered.';
            } else {
                $error = 'Unable to update user. Please try again.';
            }
        }
    }

    $user['name'] = $name;
    $user['email'] = $email;
    $user['role'] = $role;
}

$page_title = 'Edit User';
include __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <span class="eyebrow">ADMINISTRATION</span>
        <h1>Edit User</h1>
        <p class="muted">Update registered user information and account role.</p>
    </div>
</section>

<section class="content-panel">

    <?php if ($error !== ''): ?>
        <div class="alert error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="stack-form">

        <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

        <label>
            Full Name
            <input
                type="text"
                name="name"
                value="<?= e($user['name']) ?>"
                maxlength="100"
                required
            >
        </label>

        <label>
            Email Address
            <input
                type="email"
                name="email"
                value="<?= e($user['email']) ?>"
                maxlength="150"
                required
            >
        </label>

        <label>
            User Role
            <select name="role" required <?= $is_current_user ? 'disabled' : '' ?>>
                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>
                    Student
                </option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>
                    Admin
                </option>
            </select>
        </label>

        <?php if ($is_current_user): ?>
            <input type="hidden" name="role" value="admin">
            <p class="muted">Your own Admin role cannot be changed here.</p>
        <?php endif; ?>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button type="submit" class="btn primary">
                Save Changes
            </button>

            <a href="admin_users.php" class="btn">
                Cancel
            </a>
        </div>

    </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
