
<?php

require_once __DIR__ . '/includes/functions.php';

require_login();


// =====================================
// GET JOB DETAILS
// =====================================

$job_id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT *
    FROM jobs
    WHERE id = ? AND status = 'active'
");

$stmt->bind_param('i', $job_id);
$stmt->execute();

$job = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$job) {
    flash('error', 'Opportunity not found.');
    redirect('jobs.php');
}


// =====================================
// APPLICATION SUBMISSION
// =====================================

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cover = trim($_POST['cover_letter'] ?? '');
    $user_id = (int)$_SESSION['user_id'];

    // Check whether the student has already applied
    $stmt = $conn->prepare("
        SELECT id
        FROM applications
        WHERE user_id = ? AND job_id = ?
    ");

    $stmt->bind_param('ii', $user_id, $job_id);
    $stmt->execute();

    $existing_application = $stmt->get_result()->num_rows > 0;

    $stmt->close();

    if ($existing_application) {

        $error = 'You have already applied for this opportunity.';

    } else {

        // Insert new application
        try {

            $stmt = $conn->prepare("
                INSERT INTO applications
                    (user_id, job_id, cover_letter, status)
                VALUES (?, ?, ?, 'pending')
            ");

            $stmt->bind_param('iis', $user_id, $job_id, $cover);

            $stmt->execute();
            $stmt->close();

            flash('success', 'Application submitted successfully.');

            redirect('my_applications.php');

        } catch (mysqli_sql_exception $e) {

            error_log('Application submission error: ' . $e->getMessage());

            $error = 'Unable to submit your application. Please try again.';
        }
    }
}


// =====================================
// PAGE DISPLAY
// =====================================

$page_title = 'Apply';

include __DIR__ . '/includes/header.php';

?>

<div class="form-layout">

    <!-- Job Details -->

    <section class="content-panel">

        <span class="eyebrow">APPLICATION</span>

        <h1><?= e($job['title']) ?></h1>

        <p class="muted">
            <?= e($job['company']) ?>
            ·
            <?= e($job['location']) ?>
        </p>

        <div class="divider"></div>

        <h3>About this opportunity</h3>

        <p class="long-copy">
            <?= nl2br(e($job['description'])) ?>
        </p>

        <p>
            <b>Category:</b>
            <?= e($job['category']) ?>

            &nbsp;

            <b>Type:</b>
            <?= e($job['job_type']) ?>
        </p>

    </section>


    <!-- Application Form -->

    <section class="form-card">

        <h2>Submit application</h2>

        <?php if ($error): ?>

            <div class="alert error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <form method="post" class="stack-form">

            <label>
                Short cover letter

                <textarea
                    name="cover_letter"
                    rows="7"
                    placeholder="Tell the employer why you are interested..."
                ><?= e($_POST['cover_letter'] ?? '') ?></textarea>

            </label>

            <button type="submit" class="btn primary full">
                Submit application
            </button>

        </form>

    </section>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

