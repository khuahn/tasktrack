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

// Standard filters from right-nav modal
$filterPriority = $_GET['priority'] ?? '';
$filterAssignee = intval($_GET['assignee'] ?? 0);
$q = trim($_GET['search'] ?? '');
$assigned_start = $_GET['assigned_start'] ?? '';
$assigned_end = $_GET['assigned_end'] ?? '';
$completed_start = $_GET['completed_start'] ?? '';
$completed_end = $_GET['completed_end'] ?? '';
$useP = in_array($filterPriority, ['LOW','MID','HIGH','PRIO','PEND','DONE'], true);

// Build dynamic SQL with optional parameters
$sql = 'SELECT t.*, u.username,
        (SELECT CONCAT(u2.username, "|", DATE_FORMAT(e.created_at, "%m/%d/%y %H:%i"))
         FROM task_events e JOIN users u2 ON e.user_id=u2.id
         WHERE e.task_id=t.id AND e.event_type="RESTORE" ORDER BY e.created_at DESC LIMIT 1) AS last_restore
        FROM tasks t JOIN users u ON t.assigned_to=u.id WHERE t.priority="DONE"';
if ($useP) { $sql .= ' AND t.priority=?'; }
if ($filterAssignee > 0) { $sql .= ' AND t.assigned_to=?'; }
if ($q !== '') { $sql .= ' AND (t.name LIKE ? OR t.link LIKE ? OR EXISTS (SELECT 1 FROM notes n WHERE n.task_id=t.id AND n.note LIKE ?))'; }
if ($assigned_start) { $sql .= ' AND DATE(t.assigned_at) >= ?'; }
if ($assigned_end)   { $sql .= ' AND DATE(t.assigned_at) <= ?'; }
if ($completed_start){ $sql .= ' AND DATE(t.completed_at) >= ?'; }
if ($completed_end)  { $sql .= ' AND DATE(t.completed_at) <= ?'; }
$sql .= ' ORDER BY t.completed_at DESC';

$stmt = $conn->prepare($sql);
$bindTypes = '';
$bindValues = [];
if ($useP) { $bindTypes .= 's'; $bindValues[] = $filterPriority; }
if ($filterAssignee > 0) { $bindTypes .= 'i'; $bindValues[] = $filterAssignee; }
if ($q !== '') { $bindTypes .= 'sss'; $like = "%$q%"; $bindValues[] = $like; $bindValues[] = $like; $bindValues[] = $like; }
if ($assigned_start) { $bindTypes .= 's'; $bindValues[] = $assigned_start; }
if ($assigned_end)   { $bindTypes .= 's'; $bindValues[] = $assigned_end; }
if ($completed_start){ $bindTypes .= 's'; $bindValues[] = $completed_start; }
if ($completed_end)  { $bindTypes .= 's'; $bindValues[] = $completed_end; }
if ($bindTypes !== '') { $stmt->bind_param($bindTypes, ...$bindValues); }
$stmt->execute();
$res = $stmt->get_result();
$tasks = $res->fetch_all(MYSQLI_ASSOC);

// Get total completed tasks count
$count_stmt = $conn->prepare('SELECT COUNT(*) as total FROM tasks WHERE priority = "DONE"');
$count_stmt->execute();
$count_res = $count_stmt->get_result();
$total_completed = $count_res->fetch_assoc()['total'];
?>
<!-- Page-Specific CSS -->
<link rel="stylesheet" href="css/done.css?v=1">

<!-- Main Content Layout -->
<div class="main-content-layout">
    <!-- Left Content Area -->
    <div class="content-left">
        <!-- Page Header -->
        <div class="page-header">
            <h2 class="page-title">Total Completed Tasks</h2>
            <span class="page-counter"><?= $total_completed ?> completed tasks</span>
        </div>
    <div class="task-table-container stack-gap-md">
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
    
    <!-- Right Navigation Panel -->
    <div class="content-right">
        <?php include 'right-nav.php'; ?>
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
