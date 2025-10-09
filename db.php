<?php
// db.php - Database connection
// Configure via environment variables to avoid committing secrets
// DB_HOST, DB_USER, DB_PASS, DB_NAME
$hostname = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'tasktrack_user';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'tasktrack';

$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    // Avoid leaking credentials or server details
    http_response_code(500);
    exit('Database connection failed.');
}

// Ensure proper character set
if (method_exists($conn, 'set_charset')) {
    $conn->set_charset('utf8mb4');
}
