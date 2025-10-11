<?php
// user_done.php - User completed tasks placeholder
include 'auth.php';
// Only members; teamlead and admin redirect
if (get_user_role() !== 'member') {
    if (get_user_role() === 'teamlead') { header('Location: team_done.php'); exit; }
    if (get_user_role() === 'admin') { header('Location: all_done.php'); exit; }
    require_any_role(['member']);
}
include 'head.php';
include 'db.php';

$user_id = $_SESSION['user_id'];
// Filters
$q = trim($_GET['f_q'] ?? '');

// List completed tasks with optional search and last restore info
$sql = 'SELECT t.*, 
        (SELECT CONCAT(u.username, "|", DATE_FORMAT(e.created_at, "%m/%d/%y %H:%i"))
         FROM task_events e 
         JOIN users u ON e.user_id = u.id 
         WHERE e.task_id = t.id AND e.event_type = "RESTORE" 
         ORDER BY e.created_at DESC LIMIT 1) AS last_restore
        FROM tasks t 
        WHERE t.assigned_to = ? AND t.priority = "DONE"';
if ($q !== '') { $sql .= ' AND (t.name LIKE ? OR t.link LIKE ?)'; }
$sql .= ' ORDER BY t.completed_at DESC';

$stmt = $conn->prepare($sql);
if ($q !== '') {
    $like = "%$q%";
    $stmt->bind_param('iss', $user_id, $like, $like);
} else {
    $stmt->bind_param('i', $user_id);
}
$stmt->execute();
$res = $stmt->get_result();
$tasks = $res->fetch_all(MYSQLI_ASSOC);
?>
<!-- Page-Specific CSS -->
<link rel="stylesheet" href="css/done.css?v=1">

<div class="main-container">
    <div class="task-table-container">
        <!-- Filters -->
        <div class="filter-container">
        <form method="get" class="filters-bar">
            <div class="form-group search-input">
                <label for="doneQuery" class="sr-only">Search</label>
                <input class="form-control" id="doneQuery" type="text" name="f_q" placeholder="Search by task name or link" value="<?= htmlspecialchars($q) ?>">
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Apply</button>
                <a class="btn btn-secondary" href="user_done.php"><i class="fa fa-undo"></i> Reset</a>
            </div>
        </form>
        </div>
        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <i class="fa fa-check-circle"></i>
                <h3>No Completed Tasks</h3>
            </div>
        <?php else: ?>
            <table class="task-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Task</th>
                        <th>Notes</th>
                        <th>Completed</th>
                        <th>Assigned</th>
                        <th>Restore</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td>
                            <span class="priority-badge priority-done">
                                <i class="fa fa-check"></i> DONE
                            </span>
                        </td>
                        <td>
                            <a href="<?= htmlspecialchars($t['link']) ?>" target="_blank" class="task-link">
                                <i class="fa fa-external-link-alt"></i>
                                <?= htmlspecialchars($t['name']) ?>
                            </a>
                            <?php if (!empty($t['last_restore'])): list($restoredBy,$restoredAt) = explode('|',$t['last_restore']); ?>
                                <div class="restored-badge"><i class="fa fa-undo"></i> Restored by <?= htmlspecialchars($restoredBy) ?> on <?= htmlspecialchars($restoredAt) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="action-btn" onclick="showNotes(<?= intval($t['id']) ?>, '<?= addslashes($t['name']) ?>');return false;" title="View Notes">
                                <i class="fa-regular fa-file-lines"></i>
                            </button>
                        </td>
                        <td><div class="date-info"><span class="date-main"><?= date('m/d/y', strtotime($t['completed_at'])) ?></span></div></td>
                        <td><div class="date-info"><span class="date-main"><?= date('m/d/y - H:i', strtotime($t['assigned_at'])) ?></span></div></td>
                        <td>
                            <button class="action-btn" onclick="confirmRestore('taskmgt.php?action=restore&id=<?= intval($t['id']) ?>')" title="Restore to Active">
                                <i class="fa fa-recycle"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Notes Modal (read-only) -->
<div id="notesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-regular fa-file-lines" style="color:#008080"></i> <span id="modalTaskName">Task Notes</span></h3>
        </div>
        <div class="modal-body">
            <div class="notes-container" id="notesContent"></div>
        </div>
    </div>
</div>

<!-- Page-Specific JS -->
<script src="js/done.js"></script>

<?php include 'foot.php'; ?>
