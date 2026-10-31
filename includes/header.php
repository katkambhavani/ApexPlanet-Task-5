
<?php

require_once __DIR__ . '/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
$is_admin_layout = is_admin();

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($page_title ?? APP_NAME) ?> | <?= APP_NAME ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">

    <script defer src="<?= BASE_URL ?>/assets/js/app.js"></script>

    <style>
        /* Consistent spacing across all Admin pages */

        body.admin-layout main.container {
            padding-top: 8px !important;
        }

        body.admin-layout .page-heading {
            padding-top: 12px !important;
            margin-top: 0;
        }

        body.admin-layout .admin-page,
        body.admin-layout .admin-page-heading {
            margin-top: 0;
        }
    </style>

</head>

<body class="<?= $is_admin_layout ? 'admin-layout' : 'student-layout' ?>">

<header class="topbar">

    <a class="brand" href="<?= BASE_URL ?>/index.php">
        <span class="brand-mark">S</span> SkillBridge
    </a>

    <button class="menu-toggle" aria-label="Toggle navigation">
        ☰
    </button>

    <nav class="nav-links">

        <?php if (is_logged_in()): ?>

            <?php if (is_admin()): ?>

                <!-- Admin Navigation -->

                <a
                    href="<?= BASE_URL ?>/admin.php"
                    class="<?= $current_page === 'admin.php' ? 'active' : '' ?>"
                >
                    Admin Dashboard
                </a>

                <a
                    href="<?= BASE_URL ?>/admin_users.php"
                    class="<?= in_array($current_page, ['admin_users.php', 'admin_user_edit.php'], true) ? 'active' : '' ?>"
                >
                    Manage Users
                </a>

                <a
                    href="<?= BASE_URL ?>/admin_jobs.php"
                    class="<?= in_array($current_page, ['admin_jobs.php', 'admin_job_edit.php'], true) ? 'active' : '' ?>"
                >
                    Manage Jobs
                </a>

                <a
                    href="<?= BASE_URL ?>/admin_applications.php"
                    class="<?= $current_page === 'admin_applications.php' ? 'active' : '' ?>"
                >
                    Manage Applications
                </a>

            <?php else: ?>

                <!-- Student Navigation -->

                <a
                    href="<?= BASE_URL ?>/dashboard.php"
                    class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>"
                >
                    Dashboard
                </a>

                <a
                    href="<?= BASE_URL ?>/jobs.php"
                    class="<?= $current_page === 'jobs.php' ? 'active' : '' ?>"
                >
                    Explore Jobs
                </a>

                <a
                    href="<?= BASE_URL ?>/my_applications.php"
                    class="<?= $current_page === 'my_applications.php' ? 'active' : '' ?>"
                >
                    My Applications
                </a>

            <?php endif; ?>

            <a class="nav-cta" href="<?= BASE_URL ?>/logout.php">
                Logout
            </a>

        <?php else: ?>

            <!-- Guest Navigation -->

            <a href="<?= BASE_URL ?>/jobs.php">
                Explore Jobs
            </a>

            <a href="<?= BASE_URL ?>/login.php">
                Login
            </a>

            <a class="nav-cta" href="<?= BASE_URL ?>/register.php">
                Get Started
            </a>

        <?php endif; ?>

    </nav>

</header>

<main class="container">

    <?php if ($msg = flash('success')): ?>

        <div class="alert success">
            <?= e($msg) ?>
        </div>

    <?php endif; ?>

    <?php if ($msg = flash('error')): ?>

        <div class="alert error">
            <?= e($msg) ?>
        </div>

    <?php endif; ?>
