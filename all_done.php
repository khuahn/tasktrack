<?php
// all_done.php - All completed tasks
include 'auth.php';
// Only admin; redirect others
$role = get_user_role();
if ($role !== 'admin') {
    if ($role === 'member') { header('Location: user_done.php'); exit; }
    if ($role === 'teamlead') { header('Location: team_done.php'); exit; }
    require_role('admin');
}
include 'head.php';
include 'db.php';

// List all completed tasks with optional filters
$q = trim($_GET['f_q'] ?? '');
$filterPriority = $_GET['f_priority'] ?? '';
$useP = in_array($filterPriority, ['LOW','MID','HIGH','PRIO','PEND','DONE'], true);

$sql = 'SELECT t.*, u.username,
        (SELECT CONCAT(u2.username, "|", DATE_FORMAT(e.created_at, "%m/%d/%y %H:%i"))
         FROM task_events e JOIN users u2 ON e.user_id=u2.id
         WHERE e.task_id=t.id AND e.event_type="RESTORE" ORDER BY e.created_at DESC LIMIT 1) AS last_restore
        FROM tasks t JOIN users u ON t.assigned_to=u.id WHERE t.priority="DONE"';
if ($useP) { $sql .= ' AND t.priority=?'; }
if ($q !== '') { $sql .= ' AND (t.name LIKE ? OR t.link LIKE ?)'; }
$sql .= ' ORDER BY t.completed_at DESC';

$stmt = $conn->prepare($sql);
if ($useP && $q !== '') {
    $like = "%$q%";
    $stmt->bind_param('sss', $filterPriority, $like, $like);
} elseif ($useP) {
    $stmt->bind_param('s', $filterPriority);
} elseif ($q !== '') {
    $like = "%$q%";
    $stmt->bind_param('ss', $like, $like);
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
            <div class="form-group">
                <label for="doneFilterPriority" class="sr-only">Priority</label>
                <select class="form-control" id="doneFilterPriority" name="f_priority">
                    <option value="" <?= !$useP?'selected':'' ?>>All Status</option>
                    <option value="DONE" <?= $filterPriority==='DONE'?'selected':'' ?>>Done</option>
                    <option value="PRIO" <?= $filterPriority==='PRIO'?'selected':'' ?>>Prio</option>
                    <option value="HIGH" <?= $filterPriority==='HIGH'?'selected':'' ?>>High</option>
                    <option value="MID" <?= $filterPriority==='MID'?'selected':'' ?>>Mid</option>
                    <option value="LOW" <?= $filterPriority==='LOW'?'selected':'' ?>>Low</option>
                    <option value="PEND" <?= $filterPriority==='PEND'?'selected':'' ?>>Pending</option>
                </select>
            </div>
            <div class="form-group search-input">
                <label for="doneQuery" class="sr-only">Search</label>
                <input class="form-control" id="doneQuery" type="text" name="f_q" placeholder="Search by task name or link" value="<?= htmlspecialchars($q) ?>">
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Apply</button>
                <a class="btn btn-secondary" href="all_done.php"><i class="fa fa-undo"></i> Reset</a>
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
                        <th>User</th>
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
                        </td>
                        <td><?= htmlspecialchars($t['username']) ?></td>
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
                    <?php if (!empty($t['last_restore'])): list($restoredBy,$restoredAt) = explode('|',$t['last_restore']); ?>
                    <tr>
                        <td></td>
                        <td colspan="6">
                            <span class="restored-badge"><i class="fa fa-undo"></i> Restored by <?= htmlspecialchars($restoredBy) ?> on <?= htmlspecialchars($restoredAt) ?></span>
                        </td>
                    </tr>
                    <?php endif; ?>
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
