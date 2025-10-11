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
// List completed tasks
$stmt = $conn->prepare('SELECT * FROM tasks WHERE assigned_to = ? AND priority = "DONE" ORDER BY completed_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$tasks = $res->fetch_all(MYSQLI_ASSOC);
?>
<!-- Page-Specific CSS -->
<link rel="stylesheet" href="css/done.css?v=1">

<div class="main-container">
    <div class="task-table-container">
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
