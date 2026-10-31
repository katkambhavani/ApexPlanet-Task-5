
<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin_jobs.php');
}

$title = trim($_POST['title'] ?? '');
$company = trim($_POST['company'] ?? '');
$location = trim($_POST['location'] ?? '');
$type = trim($_POST['job_type'] ?? 'Internship');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');

if (
    $title === '' ||
    $company === '' ||
    $location === '' ||
    $category === '' ||
    $description === ''
) {
    flash('error', 'Please complete all job fields.');
    redirect('admin_jobs.php');
}

$stmt = $conn->prepare("
    INSERT INTO jobs
    (title, company, location, job_type, category, description, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    flash('error', 'Unable to prepare the job details.');
    redirect('admin_jobs.php');
}

$userId = (int)$_SESSION['user_id'];

$stmt->bind_param(
    'ssssssi',
    $title,
    $company,
    $location,
    $type,
    $category,
    $description,
    $userId
);

if ($stmt->execute()) {
    flash('success', 'New job opportunity published successfully.');
} else {
    flash('error', 'Unable to publish the job opportunity.');
}

$stmt->close();

redirect('admin_jobs.php');
