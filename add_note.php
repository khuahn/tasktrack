<?php
// add_note.php - Add a new note
include 'auth.php';
include 'db.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = intval($_POST['task_id'] ?? 0);
    $note = trim($_POST['note'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if ($task_id && $note) {
        $stmt = $conn->prepare('INSERT INTO notes (task_id, user_id, note, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('iis', $task_id, $user_id, $note);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add note']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing task ID or note']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>