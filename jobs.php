
<?php

require_once __DIR__ . '/includes/functions.php';

$page_title = 'Explore jobs';


// =====================================
// FETCH JOB CATEGORIES
// =====================================

$categories = $conn->query("
    SELECT DISTINCT category
    FROM jobs
    WHERE status = 'active'
      AND category <> ''
    ORDER BY category
");


// =====================================
// PAGE HEADER
// =====================================

include __DIR__ . '/includes/header.php';

?>

<!-- =====================================
     PAGE HEADING
===================================== -->

<div class="page-heading">

    <div>

        <span class="eyebrow">
            FIND YOUR FIT
        </span>

        <h1>Explore opportunities</h1>

        <p class="muted">
            Search internships and jobs that match your ambition.
        </p>

    </div>

</div>


<!-- =====================================
     SEARCH AND FILTERS
===================================== -->

<div class="filter-bar">

    <input
        id="jobSearch"
        type="search"
        placeholder="Search role, company or keyword..."
        aria-label="Search jobs"
    >


    <select id="categoryFilter" aria-label="Filter by category">

        <option value="">
            All categories
        </option>

        <?php if ($categories): ?>

            <?php while ($category = $categories->fetch_assoc()): ?>

                <option value="<?= e($category['category']) ?>">
                    <?= e($category['category']) ?>
                </option>

            <?php endwhile; ?>

        <?php endif; ?>

    </select>


    <select id="typeFilter" aria-label="Filter by job type">

        <option value="">All types</option>

        <option value="Internship">Internship</option>

        <option value="Full-time">Full-time</option>

        <option value="Part-time">Part-time</option>

        <option value="Remote">Remote</option>

    </select>

</div>


<!-- =====================================
     JOB RESULTS
===================================== -->

<div id="jobResults" class="job-grid">

    <div class="loading">
        Loading opportunities...
    </div>

</div>


<!-- =====================================
     JOB SEARCH JAVASCRIPT
===================================== -->

<script>
    window.SKILLBRIDGE_BASE = <?= json_encode(BASE_URL) ?>;
</script>

<script
    defer
    src="<?= BASE_URL ?>/assets/js/jobs.js">
</script>


<?php

include __DIR__ . '/includes/footer.php';

?>
