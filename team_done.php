<?php
// team_done.php - Team completed tasks
include 'auth.php';
// Only teamlead; redirect others
$role = get_user_role();
if ($role !== 'teamlead') {
    if ($role === 'member') { header('Location: user_done.php'); exit; }
    if ($role === 'admin') { header('Location: all_done.php'); exit; }
    require_role('teamlead');
}
include 'head.php';
include 'db.php';

$user_id = $_SESSION['user_id'];
// Get team id
$stmt = $conn->prepare('SELECT team_id FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$team_id = intval($row['team_id']);

// Filters
$q = trim($_GET['f_q'] ?? '');
$filterUser = intval($_GET['f_user'] ?? 0);

// List completed tasks for team with optional filters and last restore info
$sql = 'SELECT t.*, u.username,
        (SELECT CONCAT(u2.username, "|", DATE_FORMAT(e.created_at, "%m/%d/%y %H:%i"))
         FROM task_events e JOIN users u2 ON e.user_id=u2.id
         WHERE e.task_id=t.id AND e.event_type="RESTORE" ORDER BY e.created_at DESC LIMIT 1) AS last_restore
        FROM tasks t JOIN users u ON t.assigned_to=u.id 
        WHERE t.priority="DONE" AND u.team_id = ?';
if ($filterUser > 0) { $sql .= ' AND t.assigned_to = ?'; }
if ($q !== '') { $sql .= ' AND (t.name LIKE ? OR t.link LIKE ?)'; }
$sql .= ' ORDER BY t.completed_at DESC';

$stmt = $conn->prepare($sql);
if ($filterUser > 0 && $q !== '') {
    $like = "%$q%";
    $stmt->bind_param('iiss', $team_id, $filterUser, $like, $like);
} elseif ($filterUser > 0) {
    $stmt->bind_param('ii', $team_id, $filterUser);
} elseif ($q !== '') {
    $like = "%$q%";
    $stmt->bind_param('iss', $team_id, $like, $like);
} else {
    $stmt->bind_param('i', $team_id);
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
        <form method="get" class="filters-bar">
            <div class="form-group">
                <label for="teamFilterUser" class="sr-only">User</label>
                <select class="form-control" id="teamFilterUser" name="f_user">
                    <option value="0" <?= $filterUser===0?'selected':'' ?>>All Users</option>
                    <?php
                        $users = $conn->query('SELECT id, username FROM users WHERE team_id='.$team_id.' ORDER BY username ASC');
                        while ($u = $users->fetch_assoc()):
                    ?>
                        <option value="<?= intval($u['id']) ?>" <?= $filterUser===intval($u['id'])?'selected':'' ?>><?= htmlspecialchars($u['username']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group search-input">
                <label for="teamDoneQuery" class="sr-only">Search</label>
                <input class="form-control" id="teamDoneQuery" type="text" name="f_q" placeholder="Search by task name or link" value="<?= htmlspecialchars($q) ?>">
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Apply</button>
                <a class="btn btn-secondary" href="team_done.php"><i class="fa fa-undo"></i> Reset</a>
            </div>
        </form>
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
