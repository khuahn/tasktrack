<?php
// test.php - Simple test page to isolate the issue
session_start();
include 'db.php';

echo "<h1>TaskTrack Test Page</h1>";
echo "<p>Database connection: " . ($conn ? "✅ Working" : "❌ Failed") . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Session started: " . (session_status() === PHP_SESSION_ACTIVE ? "✅ Yes" : "❌ No") . "</p>";

// Test basic functionality
if ($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Users in database: " . $row['count'] . "</p>";
    }
}

echo "<hr>";
echo "<h2>Testing Head Include:</h2>";
try {
    include 'head.php';
    echo "<p>✅ head.php loaded successfully</p>";
} catch (Exception $e) {
    echo "<p>❌ head.php error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Testing Right Nav Include:</h2>";
try {
    include 'right-nav.php';
    echo "<p>✅ right-nav.php loaded successfully</p>";
} catch (Exception $e) {
    echo "<p>❌ right-nav.php error: " . $e->getMessage() . "</p>";
}
?>
