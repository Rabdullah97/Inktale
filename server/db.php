<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    // Log the real reason, show the visitor something generic.
    error_log('InkTale DB connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit('Database connection failed. Start MySQL and import database/inktale_simple_db.sql.');
}

$conn->set_charset('utf8mb4');
