
<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

// Handle application status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        flash('error', 'Invalid application.');
        redirect('admin_applications.php');
    }

    if ($action === 'update_status') {

        $status = strtolower(trim($_POST['status'] ?? ''));

        $allowed_statuses = [
            'pending',
            'shortlisted',
            'rejected',
            'accepted'
        ];

        if (!in_array($status, $allowed_statuses, true)) {
            flash('error', 'Invalid application status.');
            redirect('admin_applications.php');
        }

        $stmt = $conn->prepare(
            "UPDATE applications SET status = ? WHERE id = ?"
        );

        $stmt->bind_param('si', $status, $id);

        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            flash('success', 'Application status updated successfully.');
        } else {
            flash('error', 'Unable to update application status.');
        }

        $stmt->close();
        redirect('admin_applications.php');
    }

    if ($action === 'delete') {

        $stmt = $conn->prepare(
            "DELETE FROM applications WHERE id = ?"
        );

        $stmt->bind_param('i', $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            flash('success', 'Application deleted successfully.');
        } else {
            flash('error', 'Application could not be deleted.');
        }

        $stmt->close();
        redirect('admin_applications.php');
    }

    redirect('admin_applications.php');
}

// Fetch applications with applicant and job details
$applications = $conn->query("
    SELECT
        applications.id,
        applications.status,
        applications.applied_at,
        users.name AS applicant_name,
        users.email AS applicant_email,
        jobs.title AS job_title,
        jobs.company AS company_name
    FROM applications
    INNER JOIN users
        ON applications.user_id = users.id
    INNER JOIN jobs
        ON applications.job_id = jobs.id
    ORDER BY applications.applied_at DESC
");

$page_title = 'Manage Applications';
include __DIR__ . '/includes/header.php';
?>

<style>
.admin-applications-page {
    padding-top: 5px;
}

.admin-applications-heading {
    margin-bottom: 22px;
}

.admin-applications-heading h1 {
    margin: 4px 0;
    font-size: 32px;
    color: #172554;
}

.applications-panel {
    background: #ffffff;
    border: 1px solid #e7e9f1;
    border-radius: 15px;
    padding: 22px;
    margin-bottom: 25px;
}

.applications-panel h2 {
    margin-top: 0;
    color: #172554;
}

.application-status-form {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.application-status-form select {
    min-width: 125px;
    padding: 8px;
    border: 1px solid #e7e9f1;
    border-radius: 7px;
    background: #fff;
}

.application-actions {
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.application-status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: capitalize;
    background: #eef2ff;
    color: #4338ca;
}

.application-status.shortlisted {
    background: #e0f2fe;
    color: #0369a1;
}

.application-status.accepted {
    background: #dcfce7;
    color: #15803d;
}

.application-status.rejected {
    background: #fee2e2;
    color: #b91c1c;
}

.application-status.pending {
    background: #fef3c7;
    color: #92400e;
}

@media(max-width: 700px) {
    .applications-panel {
        padding: 14px;
    }
}
</style>

<div class="admin-applications-page">

    <section class="admin-applications-heading">
        <span class="eyebrow">ADMINISTRATION</span>
        <h1>Manage Applications</h1>
        <p class="muted">
            Review applications and manage applicant status.
        </p>
    </section>

    <section class="applications-panel">

        <h2>All Applications</h2>

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Applicant</th>
                        <th>Job</th>
                        <th>Applied Date</th>
                        <th>Current Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($applications && $applications->num_rows > 0): ?>

                    <?php while ($app = $applications->fetch_assoc()): ?>

                        <tr>

                            <td><?= (int)$app['id'] ?></td>

                            <td>
                                <strong><?= e($app['applicant_name']) ?></strong>
                                <br>
                                <small><?= e($app['applicant_email']) ?></small>
                            </td>

                            <td>
                                <strong><?= e($app['job_title']) ?></strong>
                                <br>
                                <small><?= e($app['company_name']) ?></small>
                            </td>

                            <td>
                                <?= e(date('d M Y', strtotime($app['applied_at']))) ?>
                            </td>

                            <td>
                                <span class="application-status <?= e($app['status']) ?>">
                                    <?= e(ucfirst($app['status'])) ?>
                                </span>
                            </td>

                            <td>

                                <div class="application-actions">

                                    <form method="post"
                                          action="admin_applications.php"
                                          class="application-status-form">

                                        <input type="hidden"
                                               name="action"
                                               value="update_status">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= (int)$app['id'] ?>">

                                        <select name="status" required>

                                            <option value="pending"
                                                <?= $app['status'] === 'pending' ? 'selected' : '' ?>>
                                                Pending
                                            </option>

                                            <option value="shortlisted"
                                                <?= $app['status'] === 'shortlisted' ? 'selected' : '' ?>>
                                                Shortlisted
                                            </option>

                                            <option value="rejected"
                                                <?= $app['status'] === 'rejected' ? 'selected' : '' ?>>
                                                Rejected
                                            </option>

                                            <option value="accepted"
                                                <?= $app['status'] === 'accepted' ? 'selected' : '' ?>>
                                                Accepted
                                            </option>

                                        </select>

                                        <button type="submit" class="btn primary small">
                                            Update
                                        </button>

                                    </form>

                                    <form method="post"
                                          action="admin_applications.php"
                                          onsubmit="return confirm('Are you sure you want to delete this application?');">

                                        <input type="hidden"
                                               name="action"
                                               value="delete">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= (int)$app['id'] ?>">

                                        <button type="submit" class="btn danger small">
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6">
                            No applications have been submitted yet.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
