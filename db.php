<?php
// db.php - Database connection
$hostname = 'sql101.infinityfree.com';
$username = 'if0_39835366';
$password = 'Mrbcjthrng00';
$database = 'if0_39835366_track';
$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
