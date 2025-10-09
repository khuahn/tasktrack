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
    $conn->query('UPDATE tasks SET priority="DONE", completed_at=NOW() WHERE id=' . $task_id);
    $message = 'Task marked complete.';
}
if ($action === 'restore' && $task_id) {
    $conn->query('UPDATE tasks SET priority="PEND", completed_at=NULL WHERE id=' . $task_id);
    $message = 'Task restored.';
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

// List tasks (admin: all, teamlead: own team)
if ($role === 'admin') {
    $res = $conn->query('SELECT t.*, u.username AS assigned_user FROM tasks t JOIN users u ON t.assigned_to=u.id');
} else {
    $res = $conn->query('SELECT t.*, u.username AS assigned_user FROM tasks t JOIN users u ON t.assigned_to=u.id WHERE u.team_id=' . $team_id);
}
$tasks = $res->fetch_all(MYSQLI_ASSOC);

?>
<div class="container">
    <h2><i class="fa fa-tasks" style="color:#008080"></i> Manage Tasks</h2>
    <?php if ($message): ?>
        <div style="color:green; margin-bottom:1em;"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" action="?action=add" style="margin-bottom:2em;">
        <h3>Add Task</h3>
        <input type="text" name="name" placeholder="Task Name" required>
        <input type="text" name="link" placeholder="Task Link">
        <select name="priority">
            <option value="LOW">Low</option>
            <option value="MID">Mid</option>
            <option value="HIGH">High</option>
            <option value="PRIO">Prio</option>
            <option value="PEND">Pending</option>
        </select>
        <select name="assigned_to">
            <option value="0">Select User</option>
            <?php foreach ($user_list as $id => $name): ?>
                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit"><i class="fa fa-plus" style="color:green"></i> Add</button>
    </form>
    <table style="width:100%;border-collapse:collapse;">
        <tr style="background:#008080;color:#fff;">
            <th>ID</th><th>Name</th><th>Priority</th><th>Assigned To</th><th>Status</th><th>Actions</th>
        </tr>
        <?php foreach ($tasks as $t): ?>
        <tr style="background:<?php echo $t['priority']==='DONE' ? '#ccc' : '#fff'; ?>;">
            <td><?php echo $t['id']; ?></td>
            <td><?php echo htmlspecialchars($t['name']); ?></td>
            <td><?php echo $t['priority']; ?></td>
            <td><?php echo htmlspecialchars($t['assigned_user']); ?></td>
            <td><?php echo $t['priority']==='DONE' ? '<i class="fa fa-check" style="color:green"></i> Done' : '<i class="fa fa-tasks" style="color:#008080"></i> Active'; ?></td>
            <td>
                <a href="?action=edit&id=<?php echo $t['id']; ?>"><i class="fa fa-edit" style="color:#008080"></i></a>
                <?php if ($t['priority']!=='DONE'): ?>
                <a href="?action=complete&id=<?php echo $t['id']; ?>" onclick="return confirm('Mark complete?');"><i class="fa fa-check" style="color:green"></i></a>
                <?php else: ?>
                <a href="?action=restore&id=<?php echo $t['id']; ?>" onclick="return confirm('Restore task?');"><i class="fa fa-recycle" style="color:orange"></i></a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php if ($action === 'edit' && $task_id):
        $res = $conn->query('SELECT * FROM tasks WHERE id=' . $task_id);
        $edit = $res->fetch_assoc();
    ?>
    <form method="post" action="?action=edit&id=<?php echo $task_id; ?>" style="margin-top:2em;">
        <h3>Edit Task</h3>
        <input type="text" name="name" value="<?php echo htmlspecialchars($edit['name']); ?>" required>
        <input type="text" name="link" value="<?php echo htmlspecialchars($edit['link']); ?>">
        <select name="priority">
            <option value="LOW" <?php if ($edit['priority']==='LOW') echo 'selected'; ?>>Low</option>
            <option value="MID" <?php if ($edit['priority']==='MID') echo 'selected'; ?>>Mid</option>
            <option value="HIGH" <?php if ($edit['priority']==='HIGH') echo 'selected'; ?>>High</option>
            <option value="PRIO" <?php if ($edit['priority']==='PRIO') echo 'selected'; ?>>Prio</option>
            <option value="PEND" <?php if ($edit['priority']==='PEND') echo 'selected'; ?>>Pending</option>
            <option value="DONE" <?php if ($edit['priority']==='DONE') echo 'selected'; ?>>Done</option>
        </select>
        <select name="assigned_to">
            <option value="0">Select User</option>
            <?php foreach ($user_list as $id => $name): ?>
                <option value="<?php echo $id; ?>" <?php if ($edit['assigned_to']==$id) echo 'selected'; ?>><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit"><i class="fa fa-save" style="color:#008080"></i> Save</button>
    </form>
    <?php endif; ?>
</div>
<?php include 'foot.php'; ?>
