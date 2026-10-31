
<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$totalUsers = (int)$conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalJobs = (int)$conn->query("SELECT COUNT(*) AS total FROM jobs")->fetch_assoc()['total'];
$totalApps = (int)$conn->query("SELECT COUNT(*) AS total FROM applications")->fetch_assoc()['total'];

$chart = $conn->query("
    SELECT status, COUNT(*) AS total
    FROM applications
    GROUP BY status
");

$labels = [];
$values = [];

while ($row = $chart->fetch_assoc()) {
    $labels[] = $row['status'];
    $values[] = (int)$row['total'];
}

$page_title = 'Admin Dashboard';
include __DIR__ . '/includes/header.php';
?>

<style>
.admin-welcome {
    background: linear-gradient(120deg, #172554, #2563eb);
    color: white;
    padding: 32px;
    border-radius: 16px;
    margin-bottom: 28px;
}

.admin-welcome h1 {
    color: white;
    margin: 8px 0;
}

.admin-welcome p {
    color: #dbeafe;
    margin: 0;
}

.admin-welcome .eyebrow {
    color: #bfdbfe;
}

.admin-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.admin-stat {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 24px;
}

.admin-stat span {
    color: #64748b;
    font-size: 14px;
}

.admin-stat strong {
    display: block;
    font-size: 34px;
    color: #172554;
    margin-top: 12px;
}

.admin-stat .stat-icon {
    font-size: 25px;
    margin-bottom: 12px;
}

.admin-panel {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 25px;
    margin-top: 28px;
}

.admin-panel h2 {
    color: #172554;
    margin: 0 0 20px;
}

.admin-empty {
    color: #64748b;
    text-align: center;
    padding: 30px;
}

@media (max-width: 750px) {
    .admin-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="admin-welcome">
    <span class="eyebrow">SKILLBRIDGE CONTROL CENTER</span>
    <h1>Welcome, Admin!</h1>
    <p>Monitor your platform activity from one place.</p>
</section>

<section class="admin-stats">

    <div class="admin-stat">
        <div class="stat-icon">👥</div>
        <span>Registered Users</span>
        <strong><?= $totalUsers ?></strong>
    </div>

    <div class="admin-stat">
        <div class="stat-icon">💼</div>
        <span>Total Job Listings</span>
        <strong><?= $totalJobs ?></strong>
    </div>

    <div class="admin-stat">
        <div class="stat-icon">📄</div>
        <span>Total Applications</span>
        <strong><?= $totalApps ?></strong>
    </div>

</section>

<section class="admin-panel">
    <h2>Application Analytics</h2>

    <?php if ($totalApps > 0): ?>
        <div style="max-width:650px;margin:auto;">
            <canvas id="applicationChart" height="250"></canvas>
        </div>
    <?php else: ?>
        <p class="admin-empty">No applications available for analytics yet.</p>
    <?php endif; ?>
</section>

<?php if ($totalApps > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('applicationChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            data: <?= json_encode($values) ?>,
            backgroundColor: [
                '#2563eb',
                '#0f766e',
                '#f59e0b',
                '#8b5cf6',
                '#ef4444'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
