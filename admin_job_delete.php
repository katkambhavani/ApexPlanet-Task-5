
<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin_jobs.php');
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    flash('error', 'Invalid job selected.');
    redirect('admin_jobs.php');
}

$stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");

if (!$stmt) {
    flash('error', 'Unable to prepare the delete request.');
    redirect('admin_jobs.php');
}

$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        flash('success', 'Job opportunity deleted successfully.');
    } else {
        flash('error', 'Job opportunity not found.');
    }
} else {
    flash('error', 'Unable to delete the job opportunity.');
}

$stmt->close();

redirect('admin_jobs.php');
