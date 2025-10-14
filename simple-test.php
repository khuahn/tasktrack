<?php
// simple-test.php - Ultra basic test
echo "<h1>Basic PHP Test</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test session
session_start();
echo "<p>Session started: " . (session_status() === PHP_SESSION_ACTIVE ? "Yes" : "No") . "</p>";

// Test database
include 'db.php';
if ($conn) {
    echo "<p>Database: Connected</p>";
    
    // Test basic query
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "<p>Tables found:</p><ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>Database: Failed</p>";
}

echo "<hr>";
echo "<h2>Testing Head Include:</h2>";
try {
    include 'head.php';
    echo "<p>✅ head.php loaded</p>";
} catch (Exception $e) {
    echo "<p>❌ head.php error: " . $e->getMessage() . "</p>";
}
?>
