
<?php

require_once __DIR__ . '/config.php';

// Enable MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    // Connect to the SkillBridge database
    $conn = new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME
    );

    // Set character encoding
    $conn->set_charset('utf8mb4');

} catch (mysqli_sql_exception $e) {

    http_response_code(500);

    exit(
        'Database connection failed. ' .
        'Check config/config.php and import database.sql.'
    );

}

?>
