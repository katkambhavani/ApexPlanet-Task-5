
<?php

require_once __DIR__ . '/includes/functions.php';

require_login();

$user_id = (int)$_SESSION['user_id'];


// =====================================
// FETCH STUDENT APPLICATIONS
// =====================================

$stmt = $conn->prepare("
    SELECT
        a.status,
        a.applied_at,
        a.cover_letter,
        j.title,
        j.company,
        j.location
    FROM applications a
    INNER JOIN jobs j ON j.id = a.job_id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
");

$stmt->bind_param('i', $user_id);
$stmt->execute();

$rows = $stmt->get_result();


// =====================================
// PAGE HEADER
// =====================================

$page_title = 'My applications';

include __DIR__ . '/includes/header.php';

?>

<!-- =====================================
     PAGE HEADING
===================================== -->

<div class="page-heading">

    <div>

        <span class="eyebrow">
            YOUR PROGRESS
        </span>

        <h1>My applications</h1>

        <p class="muted">
            Keep track of every opportunity you have applied for.
        </p>

    </div>

    <a class="btn primary" href="jobs.php">
        Find more jobs
    </a>

</div>


<!-- =====================================
     APPLICATIONS TABLE
===================================== -->

<div class="content-panel">

    <?php if ($rows->num_rows > 0): ?>

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>
                        <th>Position</th>
                        <th>Company</th>
                        <th>Location</th>
                        <th>Applied on</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody>

                    <?php while ($application = $rows->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= e($application['title']) ?>
                            </td>

                            <td>
                                <?= e($application['company']) ?>
                            </td>

                            <td>
                                <?= e($application['location']) ?>
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

            <h3>No applications yet</h3>

            <p>
                When you apply for a role, it will appear here.
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
