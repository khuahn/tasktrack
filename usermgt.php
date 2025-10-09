<?php
// usermgt.php - User management placeholder
include 'auth.php';
require_role('admin');
include 'head.php';
include 'db.php';

// Handle actions: add, edit, freeze, delete
$action = $_GET['action'] ?? '';
$user_id = intval($_GET['id'] ?? 0);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        require_csrf_post();
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'member';
        $team_id = intval($_POST['team_id'] ?? 0);
        if ($username && $password && $role) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, password, role, team_id) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('sssi', $username, $hash, $role, $team_id);
            if ($stmt->execute()) {
                $message = 'User added.';
            } else {
                $message = 'Error adding user.';
            }
            $stmt->close();
        } else {
            $message = 'All fields required.';
        }
    }
    if ($action === 'edit' && $user_id) {
        require_csrf_post();
        $role = $_POST['role'] ?? '';
        $team_id = intval($_POST['team_id'] ?? 0);
        $frozen = isset($_POST['frozen']) ? 1 : 0;
        $stmt = $conn->prepare('UPDATE users SET role=?, team_id=?, frozen=? WHERE id=?');
        $stmt->bind_param('siii', $role, $team_id, $frozen, $user_id);
        if ($stmt->execute()) {
            $message = 'User updated.';
        } else {
            $message = 'Error updating user.';
        }
        $stmt->close();
    }
}
if ($action === 'freeze' && $user_id) {
    // Use prepared statement to avoid injection
    $stmt = $conn->prepare('UPDATE users SET frozen=1 WHERE id=?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    $message = 'User frozen.';
}
if ($action === 'delete' && $user_id) {
    // Use prepared statement to avoid injection
    $stmt = $conn->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    $message = 'User deleted.';
}

// List teams for dropdown
$teams = [];
$res = $conn->query('SELECT id, name FROM teams');
while ($row = $res->fetch_assoc()) {
    $teams[$row['id']] = $row['name'];
}

// List users
$res = $conn->query('SELECT u.id, u.username, u.role, u.team_id, u.frozen, t.name AS team FROM users u LEFT JOIN teams t ON u.team_id = t.id');
$users = $res->fetch_all(MYSQLI_ASSOC);

?>
<div class="container">
    <h2><i class="fa fa-users" style="color:#008080"></i> Manage Users</h2>
    <?php if ($message): ?>
        <div style="color:green; margin-bottom:1em;"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" action="?action=add" style="margin-bottom:2em;">
        <?php echo csrf_input(); ?>
        <h3>Add User</h3>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role">
            <option value="member">Member</option>
            <option value="teamlead">Team Lead</option>
        </select>
        <select name="team_id">
            <option value="0">No Team</option>
            <?php foreach ($teams as $id => $name): ?>
                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit"><i class="fa fa-plus" style="color:green"></i> Add</button>
    </form>
    <table style="width:100%;border-collapse:collapse;">
        <tr style="background:#008080;color:#fff;">
            <th>ID</th><th>Username</th><th>Role</th><th>Team</th><th>Status</th><th>Actions</th>
        </tr>
        <?php foreach ($users as $u): ?>
        <tr style="background:<?php echo $u['frozen'] ? '#ccc' : '#fff'; ?>;">
            <td><?php echo $u['id']; ?></td>
            <td><?php echo htmlspecialchars($u['username']); ?></td>
            <td><?php echo $u['role']; ?></td>
            <td><?php echo $u['team'] ?: 'None'; ?></td>
            <td><?php echo $u['frozen'] ? '<i class="fa fa-snowflake" style="color:blue"></i> Frozen' : '<i class="fa fa-check" style="color:green"></i> Active'; ?></td>
            <td>
                <a href="?action=edit&id=<?php echo $u['id']; ?>"><i class="fa fa-edit" style="color:#008080"></i></a>
                <a href="?action=freeze&id=<?php echo $u['id']; ?>" onclick="return confirm('Freeze user?');"><i class="fa fa-snowflake" style="color:blue"></i></a>
                <a href="?action=delete&id=<?php echo $u['id']; ?>" onclick="return confirm('Delete user?');"><i class="fa fa-trash" style="color:red"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php if ($action === 'edit' && $user_id):
        $res = $conn->query('SELECT * FROM users WHERE id=' . $user_id);
        $edit = $res->fetch_assoc();
    ?>
    <form method="post" action="?action=edit&id=<?php echo $user_id; ?>" style="margin-top:2em;">
        <?php echo csrf_input(); ?>
        <h3>Edit User</h3>
        <select name="role">
            <option value="member" <?php if ($edit['role']==='member') echo 'selected'; ?>>Member</option>
            <option value="teamlead" <?php if ($edit['role']==='teamlead') echo 'selected'; ?>>Team Lead</option>
        </select>
        <select name="team_id">
            <option value="0">No Team</option>
            <?php foreach ($teams as $id => $name): ?>
                <option value="<?php echo $id; ?>" <?php if ($edit['team_id']==$id) echo 'selected'; ?>><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach; ?>
        </select>
        <label><input type="checkbox" name="frozen" value="1" <?php if ($edit['frozen']) echo 'checked'; ?>> Frozen</label>
        <button type="submit"><i class="fa fa-save" style="color:#008080"></i> Save</button>
    </form>
    <?php endif; ?>
</div>
<?php include 'foot.php'; ?>
