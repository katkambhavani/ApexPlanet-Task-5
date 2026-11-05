
<?php

require_once __DIR__ . '/includes/functions.php';

$page_title = 'Find your next opportunity';


// =====================================
// FETCH FEATURED JOBS
// =====================================

$featured = $conn->query("
    SELECT
        id,
        title,
        company,
        location,
        job_type,
        category
    FROM jobs
    WHERE status = 'active'
    ORDER BY created_at DESC
    LIMIT 3
");


// =====================================
// PAGE HEADER
// =====================================

include __DIR__ . '/includes/header.php';

?>

<!-- =====================================
     HERO SECTION
===================================== -->

<section class="hero">

    <div class="hero-copy">

        <span class="eyebrow">
            YOUR CAREER STARTS HERE
        </span>

        <h1>
            Find work that moves <em>you forward.</em>
        </h1>

        <p>
            Discover internships and jobs, build your experience,
            and take the next step in your career.
        </p>

        <div class="hero-actions">

            <a class="btn primary" href="jobs.php">
                Explore opportunities
            </a>

            <a class="btn ghost" href="register.php">
                Create free account
            </a>

        </div>

        <div class="hero-meta">

            <span>✓ Student-friendly roles</span>

            <span>✓ Track applications</span>

            <span>✓ One simple platform</span>

        </div>

    </div>


    <!-- Hero Illustration -->

    <div class="hero-art">

        <div class="art-card card-a">

            <span class="art-icon">✦</span>

            <b>New opportunity</b>

            <small>Your next chapter awaits</small>

        </div>


        <div class="art-card card-b">

            <div class="avatar-stack">

                <i>AK</i>

                <i>RM</i>

                <i>JS</i>

            </div>

            <b>Career community</b>

            <small>Connect. Apply. Grow.</small>

        </div>


        <div class="orbit">
            SB
        </div>

    </div>

</section>


<!-- =====================================
     FEATURES SECTION
===================================== -->

<section class="section">

    <div class="section-heading">

        <div>

            <span class="eyebrow">
                GET STARTED
            </span>

            <h2>Make your next move count</h2>

        </div>

        

    </div>


    <div class="feature-grid">

        <article class="feature-card">

            <div class="feature-icon">⌕</div>

            <h3>Discover opportunities</h3>

            <p>
                Search roles by keyword, category, and location.
            </p>

        </article>


        <article class="feature-card">

            <div class="feature-icon">↗</div>

            <h3>Apply with confidence</h3>

            <p>
                Submit applications and keep everything organized.
            </p>

        </article>


        <article class="feature-card">

            <div class="feature-icon">▥</div>

            <h3>Track your progress</h3>

            <p>
                See your application status in one dashboard.
            </p>

        </article>

    </div>

</section>


<!-- =====================================
     FEATURED JOBS SECTION
===================================== -->

<section class="section">

    <div class="section-heading">

        <div>

            <span class="eyebrow">
                LATEST OPENINGS
            </span>

            <h2>Featured opportunities</h2>

        </div>
 <a class="text-link" href="jobs.php">
            View all jobs →
        </a>
    </div>


    <div class="job-grid">

        <?php if ($featured && $featured->num_rows > 0): ?>

            <?php while ($job = $featured->fetch_assoc()): ?>

                <article class="job-card">

                    <div class="company-logo">
                        <?= e(strtoupper(substr($job['company'], 0, 1))) ?>
                    </div>

                    <span class="pill">
                        <?= e($job['job_type']) ?>
                    </span>

                    <h3>
                        <?= e($job['title']) ?>
                    </h3>

                    <p>
                        <?= e($job['company']) ?>
                        ·
                        <?= e($job['location']) ?>
                    </p>

                    <a class="text-link" href="jobs.php">
                        View details →
                    </a>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-state">
                New opportunities will appear here soon.
            </div>

        <?php endif; ?>

    </div>

</section>


<?php

include __DIR__ . '/includes/footer.php';

?>
