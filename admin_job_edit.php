<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    flash('error', 'Invalid job selected.');
    redirect('admin_jobs.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $job_type = trim($_POST['job_type'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $valid_types = ['Internship', 'Full-time', 'Part-time', 'Remote'];
    $valid_statuses = ['active', 'closed'];

    if (
        $title === '' ||
        $company === '' ||
        $location === '' ||
        $category === '' ||
        $description === ''
    ) {
        $error = 'Please complete all fields.';
    } elseif (
        !in_array($job_type, $valid_types, true) ||
        !in_array($status, $valid_statuses, true)
    ) {
        $error = 'Please select valid job type and status.';
    } else {
        $stmt = $conn->prepare("
            UPDATE jobs
            SET title = ?,
                company = ?,
                location = ?,
                job_type = ?,
                category = ?,
                description = ?,
                status = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            'sssssssi',
            $title,
            $company,
            $location,
            $job_type,
            $category,
            $description,
            $status,
            $id
        );

        $stmt->execute();
        $stmt->close();

        flash('success', 'Job opportunity updated successfully.');
        redirect('admin_jobs.php');
    }
}

$stmt = $conn->prepare("
    SELECT id, title, company, location, job_type, category, description, status
    FROM jobs
    WHERE id = ?
");

$stmt->bind_param('i', $id);
$stmt->execute();

$result = $stmt->get_result();
$job = $result->fetch_assoc();

$stmt->close();

if (!$job) {
    flash('error', 'Job opportunity not found.');
    redirect('admin_jobs.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error !== '') {
    $job['title'] = $title;
    $job['company'] = $company;
    $job['location'] = $location;
    $job['job_type'] = $job_type;
    $job['category'] = $category;
    $job['description'] = $description;
    $job['status'] = $status;
}

$page_title = 'Edit Job';
include __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <span class="eyebrow">ADMINISTRATION</span>
        <h1>Edit Job Opportunity</h1>
        <p class="muted">Update the job and internship details.</p>
    </div>
</section>

<section class="content-panel">

    <?php if ($error !== ''): ?>
        <div class="alert error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="stack-form">

        <input type="hidden" name="id" value="<?= (int)$job['id'] ?>">

        <label>
            Job Title
            <input
                type="text"
                name="title"
                value="<?= e($job['title']) ?>"
                required
            >
        </label>

        <label>
            Company
            <input
                type="text"
                name="company"
                value="<?= e($job['company']) ?>"
                required
            >
        </label>

        <label>
            Location
            <input
                type="text"
                name="location"
                value="<?= e($job['location']) ?>"
                required
            >
        </label>

        <label>
            Job Type
            <select name="job_type" required>
                <?php foreach (['Internship', 'Full-time', 'Part-time', 'Remote'] as $type): ?>
                    <option
                        value="<?= e($type) ?>"
                        <?= $job['job_type'] === $type ? 'selected' : '' ?>
                    >
                        <?= e($type) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Category
            <input
                type="text"
                name="category"
                value="<?= e($job['category']) ?>"
                required
            >
        </label>

        <label>
            Description
            <textarea name="description" rows="5" required><?= e($job['description']) ?></textarea>
        </label>

        <label>
            Status
            <select name="status" required>
                <option value="active" <?= $job['status'] === 'active' ? 'selected' : '' ?>>
                    Active
                </option>
                <option value="closed" <?= $job['status'] === 'closed' ? 'selected' : '' ?>>
                    Closed
                </option>
            </select>
        </label>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button type="submit" class="btn primary">
                Save Changes
            </button>

            <a href="admin_jobs.php" class="btn">
                Cancel
            </a>
        </div>

    </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>