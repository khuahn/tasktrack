<?php
// team_done.php - Team completed tasks placeholder
include 'auth.php';
require_role('teamlead');
include 'head.php';
include 'db.php';

$user_id = $_SESSION['user_id'];
// Get team id
$res = $conn->query('SELECT team_id FROM users WHERE id=' . $user_id);
$row = $res->fetch_assoc();
$team_id = intval($row['team_id']);
// List completed tasks for team
$res = $conn->query('SELECT t.*, u.username FROM tasks t JOIN users u ON t.assigned_to=u.id WHERE t.priority="DONE" AND u.team_id=' . $team_id . ' ORDER BY t.completed_at DESC');
$tasks = $res->fetch_all(MYSQLI_ASSOC);
?>
<div class="container">
    <h2><i class="fa fa-users" style="color:#008080"></i> Team Completed Tasks</h2>
    <table style="width:100%;border-collapse:collapse;">
        <tr style="background:#008080;color:#fff;">
            <th>Priority</th><th>Task</th><th>User</th><th>Notes</th><th>Completed</th><th>Assigned</th><th>Restore</th>
        </tr>
        <?php foreach ($tasks as $t): ?>
        <tr>
            <td><i class="fa fa-check" style="color:green"></i> Done</td>
            <td><a href="<?php echo htmlspecialchars($t['link']); ?>" target="_blank" style="color:#008080;"><i class="fa fa-link"></i> <?php echo htmlspecialchars($t['name']); ?></a></td>
            <td><?php echo htmlspecialchars($t['username']); ?></td>
            <td><a href="#" onclick="showNotes(<?php echo $t['id']; ?>);return false;"><i class="fa fa-sticky-note" style="color:#008080"></i></a></td>
            <td><?php echo date('m/d/y', strtotime($t['completed_at'])); ?></td>
            <td><?php echo date('m/d/y - H:i', strtotime($t['assigned_at'])); ?></td>
            <td><a href="taskmgt.php?action=restore&id=<?php echo $t['id']; ?>" onclick="return confirm('Restore task?');"><i class="fa fa-recycle" style="color:orange"></i></a></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <!-- Notes Modal (read-only) -->
    <div id="notesModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;">
        <div style="background:#fff;padding:2em;border-radius:8px;max-width:400px;width:90%;max-height:80vh;overflow-y:auto;">
            <h3><i class="fa fa-sticky-note" style="color:#008080"></i> Notes</h3>
            <div id="notesContent"></div>
            <button onclick="closeNotes()" style="margin-top:1em;background:#ccc;border:none;padding:0.5em 2em;border-radius:4px;">Close</button>
        </div>
    </div>
</div>
<script>
function showNotes(taskId) {
    document.getElementById('notesModal').style.display = 'flex';
    fetch('notes.php?task_id=' + taskId)
        .then(r => r.json())
        .then(notes => {
            let html = '';
            notes.forEach(n => {
                html += `<div style='border-bottom:1px solid #eee;padding:0.5em 0;'>`
                    + `<b>${n.username}</b> <span style='color:#888;'>${formatDate(n.created_at)}</span><br>`
                    + `<span>${n.note}</span></div>`;
            });
            document.getElementById('notesContent').innerHTML = html;
        });
}
function closeNotes() {
    document.getElementById('notesModal').style.display = 'none';
}
function formatDate(dt) {
    let d = new Date(dt.replace(' ','T'));
    return `${d.getMonth()+1}/${d.getDate()}/${d.getFullYear().toString().slice(-2)} - ${d.getHours().toString().padStart(2,'0')}:${d.getMinutes().toString().padStart(2,'0')}`;
}
</script>
<?php include 'foot.php'; ?>
