<?php
// index.php - User dashboard
include 'auth.php';
require_any_role(['teamlead','member']);
include 'head.php'; // This now loads main.css, head.css, main.js, head.js
include 'db.php';

// Get current user info
$user_id = $_SESSION['user_id'];
$role = get_user_role();

// Handle search filters
$search_priority = $_GET['priority'] ?? '';
$search_text = trim($_GET['search'] ?? '');
$assigned_start = $_GET['assigned_start'] ?? '';
$assigned_end = $_GET['assigned_end'] ?? '';
$completed_start = $_GET['completed_start'] ?? '';
$completed_end = $_GET['completed_end'] ?? '';

// Build search conditions
$conditions = ["t.assigned_to = " . intval($user_id), "t.priority <> 'DONE'"];

if ($search_priority && in_array($search_priority, ['LOW','MID','HIGH','PRIO','PEND','DONE'])) {
    $conditions[] = "t.priority = '" . $conn->real_escape_string($search_priority) . "'";
}

if ($search_text) {
    $like = '%' . $conn->real_escape_string($search_text) . '%';
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

// Hosting-safe query (avoid get_result) with equivalent sorting
$uid = intval($user_id);
$whereSql = implode(' AND ', $conditions);
$sql = "
    SELECT t.*, COALESCE(nc.note_count, 0) AS note_count
    FROM tasks t
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
$tasks = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

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
    
    // If same day
    if ($days == 0) {
        return "Today (0 days ago)";
    }
    // If yesterday
    elseif ($days == 1) {
        return "Yesterday (1 day ago)";
    }
    // Older dates
    else {
        $formattedDate = $date->format('m/d/y');
        return "$formattedDate ($days days ago)";
    }
}
?>

<!-- Page-Specific CSS (loaded after global and component CSS) -->
<link rel="stylesheet" href="css/index.css?v=2">

<!-- Main Content Layout -->
<div class="main-content-layout">
    <!-- Left Content Area -->
    <div class="content-left">
        <!-- Page Header -->
        <div class="page-header">
            <h2 class="page-title">User Tasks</h2>
            <span class="page-counter"><?= count($tasks) ?> active tasks</span>
        </div>
        
        <!-- Task Table Container -->
        <div class="task-table-container">
        <?php if (empty($tasks)): ?>
            <!-- Empty state when no tasks are available -->
            <div class="empty-state">
                <i class="fa fa-check-circle"></i>
                <h3>No Active Tasks</h3>
                <p>All tasks are completed! Great work!</p>
            </div>
        <?php else: ?>
            <!-- Tasks table -->
            <table class="task-table">
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Task</th>
                        <th>Notes</th>
                        <th>Updated</th>
                        <th>Assigned</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t): 
                        // Get priority styling data or default to LOW if not found
                        $priority = $t['priority'];
                        $priorityInfo = $priorityData[$priority] ?? $priorityData['LOW'];
                        $hasNotes = $t['note_count'] > 0;
                    ?>
                    <tr>
                        <!-- Priority column with badge -->
                        <td>
                            <span class="priority-badge <?= $priorityInfo['class'] ?>">
                                <i class="fa <?= $priorityInfo['icon'] ?>"></i>
                                <?= htmlspecialchars($priority) ?>
                            </span>
                        </td>
                        
                        <!-- Task name with external link -->
                        <td>
                            <a href="<?= htmlspecialchars($t['link']) ?>" target="_blank" class="task-link">
                                <i class="fa fa-external-link-alt"></i>
                                <?= htmlspecialchars($t['name']) ?>
                            </a>
                        </td>
                        
                        <!-- Notes button with bigger file-lines icon -->
                        <!-- Notes button with task name passed to showNotes function -->
<td>
    <button class="action-btn" onclick="showNotes(<?= $t['id'] ?>, '<?= addslashes($t['name']) ?>');return false;" title="View/Add Notes">
        <i class="fa-regular fa-file-lines notes-icon"></i>
    </button>
</td>
                        
                        <!-- UPDATED Column: NO ICON - just show text (not bold) -->
                        <td>
                            <div class="date-info">
                                <span class="date-main">
                                    <?= $hasNotes ? formatTaskDate($t['updated_at']) : 'For Submission' ?>
                                </span>
                            </div>
                        </td>
                        
                        <!-- ASSIGNED Column: Always show formatted date (not bold) -->
                        <td>
                            <div class="date-info">
                                <span class="date-main"><?= formatTaskDate($t['assigned_at']) ?></span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Right Navigation Panel -->
    <div class="content-right">
        <?php include 'right-nav.php'; ?>
    </div>
</div>

<!-- Notes Modal - for viewing and adding task notes -->
<div id="notesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-regular fa-file-lines" style="color:#008080"></i> <span id="modalTaskName">Task Notes</span></h3>
        </div>
        <div class="modal-body">
            <!-- Notes will be dynamically loaded here -->
            <div class="notes-container" id="notesContent"></div>
            <!-- Note submission form -->
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

<!-- Page-Specific JavaScript (loaded after global and component JS) -->
<script src="js/index.js"></script>

<?php include 'foot.php'; ?>
