
<?php
require_once __DIR__ . '/../includes/functions.php';

// Get search filters
$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['category'] ?? '');
$type = trim($_GET['type'] ?? '');

// Base query
$sql = "
    SELECT
        id,
        title,
        company,
        location,
        job_type,
        category,
        description
    FROM jobs
    WHERE status = 'active'
";

$params = [];
$types = '';

// Search by title, company or description
if ($q !== '') {
    $sql .= "
        AND (
            title LIKE ?
            OR company LIKE ?
            OR description LIKE ?
        )
    ";

    $like = "%{$q}%";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;

    $types .= 'sss';
}

// Filter by category
if ($cat !== '') {
    $sql .= " AND category = ?";

    $params[] = $cat;
    $types .= 's';
}

// Filter by job type
if ($type !== '') {
    $sql .= " AND job_type = ?";

    $params[] = $type;
    $types .= 's';
}

// Sort and limit results
$sql .= " ORDER BY created_at DESC LIMIT 50";

// Prepare statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo '<div class="empty-state">Unable to load opportunities. Please try again.</div>';
    exit;
}

// Bind search parameters
if (!empty($params)) {
    $bindRefs = [];

    foreach ($params as $key => &$value) {
        $bindRefs[$key] = &$value;
    }

    $stmt->bind_param($types, ...$bindRefs);
}

// Execute query
$stmt->execute();

$res = $stmt->get_result();

// Check for matching jobs
if (!$res || $res->num_rows === 0) {
    echo '<div class="empty-state">No matching opportunities found. Try another search.</div>';

    $stmt->close();
    exit;
}

// Display job cards
while ($j = $res->fetch_assoc()):
?>

<article class="job-card">

    <div class="job-card-top">

        <div class="company-logo">
            <?= e(strtoupper(substr($j['company'], 0, 1))) ?>
        </div>

        <span class="pill">
            <?= e($j['job_type']) ?>
        </span>

    </div>

    <h3>
        <?= e($j['title']) ?>
    </h3>

    <p class="company-line">
        <?= e($j['company']) ?>
    </p>

    <p class="muted">
        <?= e($j['location']) ?> · <?= e($j['category']) ?>
    </p>

    <p class="job-description">
        <?= e(mb_strimwidth($j['description'], 0, 115, '…')) ?>
    </p>

    <div class="job-card-bottom">

        <a
            class="btn small primary"
            href="<?= BASE_URL ?>/apply.php?id=<?= (int)$j['id'] ?>"
        >
            View & Apply
        </a>

    </div>

</article>

<?php
endwhile;

$stmt->close();
?>
