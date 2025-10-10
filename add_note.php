<?php
// add_note.php - Add a new note AND update task timestamp
include 'auth.php';
include 'db.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = intval($_POST['task_id'] ?? 0);
    $note = trim($_POST['note'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if ($task_id && $note) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert the note
            $stmt = $conn->prepare('INSERT INTO notes (task_id, user_id, note, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->bind_param('iis', $task_id, $user_id, $note);
            
            if ($stmt->execute()) {
                // CRITICAL: Update task's updated_at timestamp to move it to bottom
                $update_stmt = $conn->prepare('UPDATE tasks SET updated_at = NOW() WHERE id = ?');
                $update_stmt->bind_param('i', $task_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                $conn->commit();
                echo json_encode(['success' => true]);
            } else {
                $conn->rollback();
                echo json_encode(['success' => false, 'error' => 'Failed to add note']);
            }
            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing task ID or note']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
