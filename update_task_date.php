<?php
// update_task_date.php - Update task date
include 'auth.php';
include 'db.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = intval($_POST['task_id'] ?? 0);
    $new_date = $_POST['new_date'] ?? '';
    
    if ($task_id && $new_date) {
        // Convert date to datetime format
        $new_datetime = $new_date . ' ' . date('H:i:s');
        
        $stmt = $conn->prepare('UPDATE tasks SET updated_at = ? WHERE id = ?');
        $stmt->bind_param('si', $new_datetime, $task_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update date']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing task ID or date']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>