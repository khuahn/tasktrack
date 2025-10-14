<?php
// taskmgt.php - Admin/Team Lead task management placeholder
include 'auth.php';
require_any_role(['admin','teamlead']);
include 'head.php';
include 'db.php';

$user_id = $_SESSION['user_id'];
$role = get_user_role();
$message = '';

// Get team id and name for teamlead
$team_id = 0;
$team_name = '';
if ($role === 'teamlead') {
    $res = $conn->query('SELECT u.team_id, t.name as team_name FROM users u LEFT JOIN teams t ON u.team_id = t.id WHERE u.id=' . $user_id);
    $row = $res->fetch_assoc();
    $team_id = intval($row['team_id']);
    $team_name = $row['team_name'] ?? 'Unknown Team';
}

// Get total active tasks count
$count_conditions = ["priority <> 'DONE'"];
if ($role !== 'admin') {
    $count_conditions[] = 'assigned_to IN (SELECT id FROM users WHERE team_id = ' . intval($team_id) . ')';
}
$count_sql = 'SELECT COUNT(*) as total FROM tasks WHERE ' . implode(' AND ', $count_conditions);
$count_res = $conn->query($count_sql);
$total_active_tasks = $count_res->fetch_assoc()['total'];

// Handle actions: add, edit, complete, restore
$action = $_GET['action'] ?? '';
$task_id = intval($_GET['id'] ?? 0);

// Filters (GET + session persistence)
// Reset
if (isset($_GET['f_reset']) && $_GET['f_reset'] === '1') {
    unset($_SESSION['tmgt_f_priority'], $_SESSION['tmgt_f_assigned_to'], $_SESSION['tmgt_f_q']);
}

$filterPriority = $_GET['priority'] ?? ($_SESSION['tmgt_f_priority'] ?? '');
$filterAssignedTo = intval($_GET['assignee'] ?? ($_SESSION['tmgt_f_assigned_to'] ?? 0));
$filterQuery = trim($_GET['search'] ?? ($_SESSION['tmgt_f_q'] ?? ''));
$assigned_start = $_GET['assigned_start'] ?? '';
$assigned_end = $_GET['assigned_end'] ?? '';
$completed_start = $_GET['completed_start'] ?? '';
$completed_end = $_GET['completed_end'] ?? '';

// Persist back to session for next visit
$_SESSION['tmgt_f_priority'] = $filterPriority;
$_SESSION['tmgt_f_assigned_to'] = $filterAssignedTo;
$_SESSION['tmgt_f_q'] = $filterQuery;

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
    // Restore task and log event (skip logging if table missing)
    $stmt = $conn->prepare('UPDATE tasks SET priority="PEND", completed_at=NULL WHERE id=?');
    $stmt->bind_param('i', $task_id);
    $stmt->execute();
    $stmt->close();
    // Best-effort logging
    try {
        $conn->query('CREATE TABLE IF NOT EXISTS task_events (
            id INT PRIMARY KEY AUTO_INCREMENT,
            task_id INT NOT NULL,
            user_id INT NOT NULL,
            event_type ENUM("RESTORE") NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX idx_task_events_task_created (task_id, created_at),
            INDEX idx_task_events_type_created (event_type, created_at)
        )');
        $log = $conn->prepare('INSERT INTO task_events (task_id, user_id, event_type, created_at) VALUES (?, ?, "RESTORE", NOW())');
        if ($log) {
            $log->bind_param('ii', $task_id, $user_id);
            $log->execute();
            $log->close();
        }
    } catch (Throwable $e) {
        // ignore logging failures
    }
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
    if (empty($dateString) || $dateString === '0000-00-00 00:00:00') {
        return '—';
    }
    try {
        $date = new DateTime($dateString);
    } catch (Exception $e) {
        return '—';
    }
    $now = new DateTime();
    $diff = $now->diff($date);
    $days = (int)$diff->days;

    if ($days === 0) {
        return 'Today (0 days ago)';
    }
    if ($days === 1) {
        return 'Yesterday (1 day ago)';
    }
    $formattedDate = $date->format('m/d/y');
    return "$formattedDate ($days days ago)";
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

// List tasks (admin: all, teamlead: own team), exclude DONE, include note_count,
// replicate index sort, apply filters (priority, assignee, search) using optional params
$allowedPriorities = ['LOW','MID','HIGH','PRIO','PEND'];
$usePriorityFilter = in_array($filterPriority, $allowedPriorities, true);
$useAssigneeFilter = $filterAssignedTo > 0;
$useQueryFilter = $filterQuery !== '';

// Build task list using query compatible with shared hosting

// Build query using safe string interpolation to avoid get_result dependency
$conditions = ["t.priority <> 'DONE'"];
if ($role !== 'admin') {
    $conditions[] = 'u.team_id = ' . intval($team_id);
}
if ($usePriorityFilter) {
    $conditions[] = 't.priority = \'' . $conn->real_escape_string($filterPriority) . '\'';
}
if ($useAssigneeFilter) {
    $conditions[] = 't.assigned_to = ' . intval($filterAssignedTo);
}
if ($useQueryFilter) {
    $like = '%' . $conn->real_escape_string($filterQuery) . '%';
    $conditions[] = "(t.name LIKE '$like' OR t.link LIKE '$like' OR EXISTS (SELECT 1 FROM notes n WHERE n.task_id = t.id AND n.note LIKE '$like'))";
}

if ($assigned_start) {
    $conditions[] = "DATE(t.assigned_at) >= '" . $conn->real_escape_string($assigned_start) . "'";
}

if ($assigned_end) {
    $conditions[] = "DATE(t.assigned_at) <= '" . $conn->real_escape_string($assigned_end) . "'";
}

if ($completed_start) {
    $conditions[] = "DATE(t.completed_at) >= '" . $conn->real_escape_string($completed_start) . "'";
}

if ($completed_end) {
    $conditions[] = "DATE(t.completed_at) <= '" . $conn->real_escape_string($completed_end) . "'";
}

$whereSql = implode(' AND ', $conditions);
$sql = "
    SELECT t.*, u.username AS assigned_user, COALESCE(nc.note_count, 0) AS note_count
    FROM tasks t
    JOIN users u ON t.assigned_to = u.id
    LEFT JOIN (
        SELECT task_id, COUNT(*) AS note_count
        FROM notes
        GROUP BY task_id
    ) nc ON nc.task_id = t.id
    WHERE $whereSql
    ORDER BY (COALESCE(nc.note_count, 0) = 0) DESC,
      CASE WHEN COALESCE(nc.note_count, 0) = 0 THEN t.assigned_at ELSE t.updated_at END ASC
";

$res = $conn->query($sql);
if ($res === false) {
    $message = 'Error loading tasks: ' . htmlspecialchars($conn->error);
    $tasks = [];
} else {
    $tasks = $res->fetch_all(MYSQLI_ASSOC);
}

?>
<!-- Page-Specific CSS (loaded after global and component CSS) -->
<link rel="stylesheet" href="css/tskmgt.css?v=1">

<!-- Main Content Layout -->
<div class="main-content-layout">
    <!-- Left Content Area -->
    <div class="content-left">
        <!-- Page Header -->
        <div class="page-header">
            <?php if ($role === 'admin'): ?>
                <h2 class="page-title">Total Active Tasks</h2>
            <?php else: ?>
                <h2 class="page-title"><?= htmlspecialchars($team_name) ?> Tasks</h2>
            <?php endif; ?>
            <span class="page-counter"><?= $total_active_tasks ?> active tasks</span>
        </div>
        
        <?php if ($message): ?>
            <div class="text-success" style="margin: var(--space-md);"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Tasks Table -->
        <div class="task-table-container stack-gap-md">
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
    
    <!-- Right Navigation Panel -->
    <div class="content-right">
        <?php include 'right-nav.php'; ?>
    </div>
</div>

<!-- Add Task moved to global modal in header -->

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
