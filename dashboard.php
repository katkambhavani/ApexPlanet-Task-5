
<?php

require_once __DIR__ . '/includes/functions.php';

require_login();


// =====================================
// STUDENT INFORMATION
// =====================================

$user_id = (int)$_SESSION['user_id'];


// =====================================
// APPLICATION STATISTICS
// =====================================

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status = 'pending'), 0) AS pending,
        COALESCE(SUM(status = 'shortlisted'), 0) AS shortlisted
    FROM applications
    WHERE user_id = ?
");

$stmt->bind_param('i', $user_id);
$stmt->execute();

$stats = $stmt->get_result()->fetch_assoc();

$stmt->close();


// =====================================
// RECENT APPLICATIONS
// =====================================

$stmt = $conn->prepare("
    SELECT
        a.status,
        a.applied_at,
        j.title,
        j.company,
        j.location
    FROM applications a
    INNER JOIN jobs j ON j.id = a.job_id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");

$stmt->bind_param('i', $user_id);
$stmt->execute();

$recent = $stmt->get_result();


// =====================================
// PAGE DISPLAY
// =====================================

$page_title = 'Dashboard';

include __DIR__ . '/includes/header.php';

?>

<!-- Dashboard Heading -->

<div class="page-heading">

    <div>

        <span class="eyebrow">YOUR SPACE</span>

        <h1>
            Hello, <?= e($_SESSION['user_name'] ?? 'Student') ?> 👋
        </h1>

        <p class="muted">
            Here is your opportunity dashboard.
        </p>

    </div>

    <a class="btn primary" href="jobs.php">
        Find opportunities
    </a>

</div>


<!-- Application Statistics -->

<div class="stats-grid">

    <div class="stat-card">

        <span>Total applications</span>

        <strong>
            <?= (int)($stats['total'] ?? 0) ?>
        </strong>

        <small>All submitted applications</small>

    </div>


    <div class="stat-card">

        <span>Pending review</span>

        <strong>
            <?= (int)($stats['pending'] ?? 0) ?>
        </strong>

        <small>Waiting for an update</small>

    </div>


    <div class="stat-card">

        <span>Shortlisted</span>

        <strong>
            <?= (int)($stats['shortlisted'] ?? 0) ?>
        </strong>

        <small>Keep preparing</small>

    </div>

</div>


<!-- Recent Applications -->

<div class="content-panel">

    <div class="section-heading">

        <div>

            <h2>Recent applications</h2>

            <p class="muted">
                Your latest activity
            </p>

        </div>

        <a class="text-link" href="my_applications.php">
            View all →
        </a>

    </div>


    <?php if ($recent->num_rows > 0): ?>

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>
                        <th>Role</th>
                        <th>Company</th>
                        <th>Applied</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody>

                    <?php while ($application = $recent->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= e($application['title']) ?>
                            </td>

                            <td>
                                <?= e($application['company']) ?>
                            </td>

                            <td>
                                <?= e(date(
                                    'd M Y',
                                    strtotime($application['applied_at'])
                                )) ?>
                            </td>

                            <td>

                                <span class="status <?= e($application['status']) ?>">
                                    <?= e(ucfirst($application['status'])) ?>
                                </span>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <div class="empty-state">

            <div class="big-icon">↗</div>

            <h3>
                Your journey starts with one application.
            </h3>

            <p>
                Explore opportunities and apply to roles that match your goals.
            </p>

            <a class="btn primary" href="jobs.php">
                Explore jobs
            </a>

        </div>

    <?php endif; ?>

</div>

<?php

$stmt->close();

include __DIR__ . '/includes/footer.php';

?>
