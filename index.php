<?php
// index.php - User dashboard
include 'auth.php';
require_any_role(['teamlead','member']);
include 'head.php'; // This now loads main.css, head.css, main.js, head.js
include 'db.php';

// Get current user info
$user_id = $_SESSION['user_id'];
$role = get_user_role();

// ALTERNATIVE QUERY: More explicit sorting
$stmt = $conn->prepare('
    SELECT t.*, COUNT(n.id) as note_count 
    FROM tasks t 
    LEFT JOIN notes n ON t.id = n.task_id 
    WHERE t.assigned_to = ? AND t.priority != "DONE" 
    GROUP BY t.id 
    ORDER BY 
        (COUNT(n.id) = 0) DESC,  -- Tasks without notes first (1), then with notes (0)
        CASE 
            WHEN COUNT(n.id) = 0 THEN t.assigned_at 
            ELSE t.updated_at 
        END ASC
');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

<div class="main-container">
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
