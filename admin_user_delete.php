
<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin_users.php');
}

$id = (int)($_POST['id'] ?? 0);
$current_user_id = (int)($_SESSION['user_id'] ?? 0);

if ($id <= 0) {
    flash('error', 'Invalid user selected.');
    redirect('admin_users.php');
}

if ($id === $current_user_id) {
    flash('error', 'You cannot delete your own account.');
    redirect('admin_users.php');
}

try {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        flash('success', 'User deleted successfully.');
    } else {
        flash('error', 'User not found.');
    }

    $stmt->close();

} catch (mysqli_sql_exception $e) {
    flash('error', 'Unable to delete this user. Please try again.');
}

redirect('admin_users.php');
