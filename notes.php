<?php
// notes.php - Get notes for a task
include 'auth.php';
include 'db.php';

require_login();

$task_id = intval($_GET['task_id'] ?? 0);
if (!$task_id) {
    echo json_encode([]);
    exit;
}

// Get notes with usernames
$stmt = $conn->prepare('
    SELECT n.*, u.username 
    FROM notes n 
    JOIN users u ON n.user_id = u.id 
    WHERE n.task_id = ? 
    ORDER BY n.created_at ASC
');
$stmt->bind_param('i', $task_id);
$stmt->execute();
$result = $stmt->get_result();
$notes = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($notes);
?>