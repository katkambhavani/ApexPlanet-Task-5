
<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$users = $conn->query("
    SELECT id, name, email, role, verified, created_at
    FROM users
    ORDER BY created_at DESC
");

$page_title = 'Manage Users';
include __DIR__ . '/includes/header.php';
?>

<style>
.admin-page {
    padding-top: 5px;
}

.admin-page-heading {
    margin-bottom: 22px;
}

.admin-page-heading h1 {
    margin: 4px 0;
    font-size: 32px;
    color: #172554;
}

.admin-table-panel {
    background: #fff;
    border: 1px solid #e7e9f1;
    border-radius: 15px;
    padding: 22px;
    margin-bottom: 25px;
}

.admin-table-panel h2 {
    margin-top: 0;
    color: #172554;
}
.admin-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.admin-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 75px;
    height: 34px;
    padding: 0 12px;
    font-size: 13px;
    line-height: 1;
    box-sizing: border-box;
}

.admin-actions form {
    margin: 0;
}
.admin-role {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 12px;
    font-weight: 700;
}

.admin-role.student {
    background: #e8f8ef;
    color: #168653;
}

.admin-verified {
    color: #168653;
    font-weight: 700;
}

.admin-unverified {
    color: #b7791f;
    font-weight: 700;
}

@media(max-width:650px) {
    .admin-table-panel {
        padding: 14px;
    }
}
.edit-btn {
    background: #5546e8 !important;
    border-color: #5546e8 !important;
    color: #ffffff !important;
}
.admin-actions a.edit-btn,
.admin-actions a.edit-btn:visited {
    background: transparent !important;
    color: #5546e8 !important;
    border: 1px solid #5546e8 !important;
}

.admin-actions a.edit-btn:hover {
    background: #5546e8 !important;
    color: #ffffff !important;
    border-color: #5546e8 !important;
}
</style>

<div class="admin-page">

    <section class="admin-page-heading">
        <span class="eyebrow">ADMINISTRATION</span>
        <h1>Manage Users</h1>
        <p class="muted">View, edit and manage registered SkillBridge accounts.</p>
    </section>

    <section class="admin-table-panel">

        <h2>Registered Users</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Verification</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($users && $users->num_rows > 0): ?>

                    <?php while ($user = $users->fetch_assoc()): ?>

                        <tr>
                            <td><?= (int)$user['id'] ?></td>

                            <td><?= e($user['name']) ?></td>

                            <td><?= e($user['email']) ?></td>

                            <td>
                                <span class="admin-role <?= e($user['role']) ?>">
                                    <?= e(ucfirst($user['role'])) ?>
                                </span>
                            </td>

                            <td>
                                <?php if ((int)$user['verified'] === 1): ?>
                                    <span class="admin-verified">Verified</span>
                                <?php else: ?>
                                    <span class="admin-unverified">Pending</span>
                                <?php endif; ?>
                            </td>

                            <td><?= e(date('d M Y', strtotime($user['created_at']))) ?></td>

                            <td>
                                <div class="admin-actions">

                                    <a class="btn danger small edit-btn"
                                       href="admin_user_edit.php?id=<?= (int)$user['id'] ?>">
                                        Edit
                                    </a>

                                    <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>

                                        <form method="post"
                                              action="admin_user_delete.php"
                                              onsubmit="return confirm('Are you sure you want to delete this user? Their applications will also be deleted.');">

                                            <input type="hidden"
                                                   name="id"
                                                   value="<?= (int)$user['id'] ?>">

                                            <button type="submit" class="btn danger small">
                                                Delete
                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <span class="muted">Current account</span>

                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7">No registered users found.</td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>

    </section>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
