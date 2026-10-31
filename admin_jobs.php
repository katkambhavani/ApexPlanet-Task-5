<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$jobs = $conn->query("
    SELECT id, title, company, job_type, status
    FROM jobs
    ORDER BY created_at DESC
");

$page_title = 'Manage Jobs';
include __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <div>
        <span class="eyebrow">ADMINISTRATION</span>
        <h1>Manage Jobs</h1>
        <p class="muted">Publish and manage job and internship opportunities.</p>
    </div>
</section>

<section class="content-panel">
    <h2>Publish a New Opportunity</h2>

    <form method="post" action="admin_job_save.php" class="stack-form">

        <label>
            Job Title
            <input type="text" name="title" required>
        </label>

        <label>
            Company
            <input type="text" name="company" required>
        </label>

        <label>
            Location
            <input type="text" name="location" required>
        </label>

        <label>
            Job Type
            <select name="job_type" required>
                <option value="Internship">Internship</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Remote">Remote</option>
            </select>
        </label>

        <label>
            Category
            <input type="text" name="category" placeholder="Technology" required>
        </label>

        <label>
            Description
            <textarea name="description" rows="4" required></textarea>
        </label>

        <button type="submit" class="btn primary">
            Publish Opportunity
        </button>

    </form>
</section>

<section class="content-panel" style="margin-top:25px;">
    <h2>All Job Opportunities</h2>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($jobs && $jobs->num_rows > 0): ?>
                    <?php while ($job = $jobs->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($job['title']) ?></td>
                            <td><?= e($job['company']) ?></td>
                            <td><?= e($job['job_type']) ?></td>
                            <td><?= e($job['status']) ?></td>
                            <td>
                                <div class="admin-actions" style="display:flex; gap:8px; align-items:center;">

                                    <a
                                        href="admin_job_edit.php?id=<?= (int)$job['id'] ?>"
                                        class="btn small edit-btn"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="post"
                                        action="admin_job_delete.php"
                                        onsubmit="return confirm('Delete this job and its applications?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$job['id'] ?>"
                                        >

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
                        <td colspan="5">No job opportunities found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>