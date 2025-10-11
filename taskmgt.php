<?php
// taskmgt.php - Admin/Team Lead task management placeholder
include 'auth.php';
require_any_role(['admin','teamlead']);
include 'head.php';
include 'db.php';

$user_id = $_SESSION['user_id'];
$role = get_user_role();
$message = '';

// Get team id for teamlead
$team_id = 0;
if ($role === 'teamlead') {
    $res = $conn->query('SELECT team_id FROM users WHERE id=' . $user_id);
    $row = $res->fetch_assoc();
    $team_id = intval($row['team_id']);
}

// Handle actions: add, edit, complete, restore
$action = $_GET['action'] ?? '';
$task_id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        require_csrf_post();
        $name = trim($_POST['name'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $priority = $_POST['priority'] ?? 'LOW';
        $assigned_to = intval($_POST['assigned_to'] ?? 0);
        if ($name && $assigned_to) {
            $stmt = $conn->prepare('INSERT INTO tasks (name, link, priority, assigned_to, assigned_by, assigned_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
            $stmt->bind_param('sssii', $name, $link, $priority, $assigned_to, $user_id);
            if ($stmt->execute()) {
                $message = 'Task added.';
            } else {
                $message = 'Error adding task.';
            }
            $stmt->close();
        } else {
            $message = 'All fields required.';
        }
    }
    if ($action === 'edit' && $task_id) {
        require_csrf_post();
        $name = trim($_POST['name'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $priority = $_POST['priority'] ?? 'LOW';
        $assigned_to = intval($_POST['assigned_to'] ?? 0);
        $stmt = $conn->prepare('UPDATE tasks SET name=?, link=?, priority=?, assigned_to=?, updated_at=NOW() WHERE id=?');
        $stmt->bind_param('sssii', $name, $link, $priority, $assigned_to, $task_id);
        if ($stmt->execute()) {
            $message = 'Task updated.';
        } else {
            $message = 'Error updating task.';
        }
        $stmt->close();
    }
}
if ($action === 'complete' && $task_id) {
    $stmt = $conn->prepare('UPDATE tasks SET priority="DONE", completed_at=NOW() WHERE id=?');
    $stmt->bind_param('i', $task_id);
    $stmt->execute();
    $stmt->close();
    $message = 'Task marked complete.';
}
if ($action === 'restore' && $task_id) {
    $stmt = $conn->prepare('UPDATE tasks SET priority="PEND", completed_at=NULL WHERE id=?');
    $stmt->bind_param('i', $task_id);
    $stmt->execute();
    $stmt->close();
    $message = 'Task restored.';
}

// Priority data configuration - defines classes and icons for each priority level
$priorityData = [
    'LOW' => ['class' => 'priority-low', 'icon' => 'fa-arrow-down'],
    'MID' => ['class' => 'priority-mid', 'icon' => 'fa-minus'],
    'HIGH' => ['class' => 'priority-high', 'icon' => 'fa-arrow-up'],
    'PRIO' => ['class' => 'priority-prio', 'icon' => 'fa-exclamation'],
    'PEND' => ['class' => 'priority-pend', 'icon' => 'fa-hourglass-half']
];

/**
 * Format date for display in Updated and Assigned columns
 * Returns: "Today (0 days ago)", "Yesterday (1 day ago)", or "MM/DD/YY (X days ago)"
 */
function formatTaskDate($dateString) {
    $date = new DateTime($dateString);
    $now = new DateTime();
    $diff = $now->diff($date);
    $days = $diff->days;

    if ($days == 0) {
        return "Today (0 days ago)";
    } elseif ($days == 1) {
        return "Yesterday (1 day ago)";
    } else {
        $formattedDate = $date->format('m/d/y');
        return "$formattedDate ($days days ago)";
    }
}

// List users for assignment (admin: all, teamlead: own team)
$user_list = [];
if ($role === 'admin') {
    $res = $conn->query('SELECT id, username FROM users WHERE role IN ("teamlead","member") AND frozen=0');
    while ($row = $res->fetch_assoc()) {
        $user_list[$row['id']] = $row['username'];
    }
} else {
    $res = $conn->query('SELECT id, username FROM users WHERE team_id=' . $team_id . ' AND role IN ("teamlead","member") AND frozen=0');
    while ($row = $res->fetch_assoc()) {
        $user_list[$row['id']] = $row['username'];
    }
}

// List tasks (admin: all, teamlead: own team), exclude DONE, include note_count, replicate index sort
if ($role === 'admin') {
    $stmt = $conn->prepare('
        SELECT t.*, u.username AS assigned_user, COUNT(n.id) AS note_count
        FROM tasks t
        JOIN users u ON t.assigned_to = u.id
        LEFT JOIN notes n ON t.id = n.task_id
        WHERE t.priority != "DONE"
        GROUP BY t.id
        ORDER BY
            (COUNT(n.id) = 0) DESC,
            CASE WHEN COUNT(n.id) = 0 THEN t.assigned_at ELSE t.updated_at END ASC
    ');
} else {
    $stmt = $conn->prepare('
        SELECT t.*, u.username AS assigned_user, COUNT(n.id) AS note_count
        FROM tasks t
        JOIN users u ON t.assigned_to = u.id
        LEFT JOIN notes n ON t.id = n.task_id
        WHERE t.priority != "DONE" AND u.team_id = ?
        GROUP BY t.id
        ORDER BY
            (COUNT(n.id) = 0) DESC,
            CASE WHEN COUNT(n.id) = 0 THEN t.assigned_at ELSE t.updated_at END ASC
    ');
    $stmt->bind_param('i', $team_id);
}
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<!-- Page-Specific CSS (loaded after global and component CSS) -->
<link rel="stylesheet" href="css/tskmgt.css?v=1">

<div class="main-container">
    <!-- Add Task Form -->
    <div class="task-table-container" style="padding: var(--space-md); margin-bottom: var(--space-lg);">
        <h2 style="margin-bottom: var(--space-md);"><i class="fa fa-tasks" style="color:#008080"></i> Manage Tasks</h2>
        <?php if ($message): ?>
            <div class="text-success" style="margin-bottom: var(--space-md);"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="post" action="?action=add">
            <?= csrf_input(); ?>
            <h3 class="mb-2">Add Task</h3>
            <div class="d-flex" style="gap: 0.5rem; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1 1 250px;">
                    <input class="form-control" type="text" name="name" placeholder="Task Name" required>
                </div>
                <div class="form-group" style="flex: 2 1 300px;">
                    <input class="form-control" type="text" name="link" placeholder="Task Link (optional)">
                </div>
                <div class="form-group" style="flex: 0 1 160px;">
                    <select class="form-control" name="priority">
                        <option value="LOW">Low</option>
                        <option value="MID">Mid</option>
                        <option value="HIGH">High</option>
                        <option value="PRIO">Prio</option>
                        <option value="PEND">Pending</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 0 1 220px;">
                    <select class="form-control" name="assigned_to" required>
                        <option value="0">Select User</option>
                        <?php foreach ($user_list as $id => $name): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="btn-group" style="align-items: flex-start;">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Add</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tasks Table -->
    <div class="task-table-container">
        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <i class="fa fa-check-circle"></i>
                <h3>No Active Tasks</h3>
                <p>All tasks are completed! Great work!</p>
            </div>
        <?php else: ?>
            <table class="task-table">
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Task</th>
                        <th>Notes</th>
                        <th>Assigned To</th>
                        <th>Updated</th>
                        <th>Assigned</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t):
                        $priority = $t['priority'];
                        $priorityInfo = $priorityData[$priority] ?? $priorityData['LOW'];
                        $hasNotes = intval($t['note_count'] ?? 0) > 0;
                    ?>
                    <tr>
                        <td>
                            <span class="priority-badge <?= $priorityInfo['class'] ?>">
                                <i class="fa <?= $priorityInfo['icon'] ?>"></i>
                                <?= htmlspecialchars($priority) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= htmlspecialchars($t['link']) ?>" target="_blank" class="task-link">
                                <i class="fa fa-external-link-alt"></i>
                                <?= htmlspecialchars($t['name']) ?>
                            </a>
                        </td>
                        <td>
                            <button class="action-btn" onclick="showNotes(<?= intval($t['id']) ?>, '<?= addslashes($t['name']) ?>');return false;" title="View/Add Notes">
                                <i class="fa-regular fa-file-lines notes-icon"></i>
                                <span style="margin-left:6px; font-size:0.85rem; color: var(--gray-700);">(<?= intval($t['note_count'] ?? 0) ?>)</span>
                            </button>
                        </td>
                        <td><?= htmlspecialchars($t['assigned_user']) ?></td>
                        <td>
                            <div class="date-info">
                                <span class="date-main">
                                    <?= $hasNotes ? formatTaskDate($t['updated_at']) : 'For Submission' ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="date-info">
                                <span class="date-main"><?= formatTaskDate($t['assigned_at']) ?></span>
                            </div>
                        </td>
                        <td>
                            <button class="action-btn" title="Edit Task" onclick="openEdit(<?= intval($t['id']) ?>, '<?= addslashes($t['name']) ?>', '<?= addslashes($t['link']) ?>', '<?= htmlspecialchars($t['priority']) ?>', <?= intval($t['assigned_to']) ?>); return false;">
                                <i class="fa fa-edit"></i>
                            </button>
                            <a class="action-btn" href="?action=complete&id=<?= intval($t['id']) ?>" onclick="return confirm('Mark complete?');" title="Mark Complete">
                                <i class="fa fa-check"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Notes Modal - for viewing and adding task notes -->
<div id="notesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-regular fa-file-lines" style="color:#008080"></i> <span id="modalTaskName">Task Notes</span></h3>
        </div>
        <div class="modal-body">
            <div class="notes-container" id="notesContent"></div>
            <form id="noteForm" method="post">
                <input type="hidden" name="task_id" id="noteTaskId">
                <div class="form-group">
                    <textarea name="note" id="noteText" class="form-control" placeholder="Add a new note..." required></textarea>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Add Note</button>
                    <button type="button" class="btn btn-secondary" onclick="closeNotes()">Close</button>
                </div>
            </form>
        </div>
    </div>
    </div>

<!-- Edit Task Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa fa-edit" style="color:#008080"></i> Edit Task</h3>
        </div>
        <div class="modal-body">
            <form id="editForm" method="post">
                <?= csrf_input(); ?>
                <input type="hidden" id="editTaskId" value="0">
                <div class="form-group">
                    <input class="form-control" type="text" name="name" id="editName" placeholder="Task Name" required>
                </div>
                <div class="form-group">
                    <input class="form-control" type="text" name="link" id="editLink" placeholder="Task Link (optional)">
                </div>
                <div class="form-group">
                    <select class="form-control" name="priority" id="editPriority">
                        <option value="LOW">Low</option>
                        <option value="MID">Mid</option>
                        <option value="HIGH">High</option>
                        <option value="PRIO">Prio</option>
                        <option value="PEND">Pending</option>
                        <option value="DONE">Done</option>
                    </select>
                </div>
                <div class="form-group">
                    <select class="form-control" name="assigned_to" id="editAssignedTo" required>
                        <option value="0">Select User</option>
                        <?php foreach ($user_list as $id => $name): ?>
                            <option value="<?= $id; ?>"><?= htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEdit()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    </div>

<!-- Page-Specific JavaScript (loaded after global and component JS) -->
<script src="js/tskmgt.js"></script>

<?php include 'foot.php'; ?>
