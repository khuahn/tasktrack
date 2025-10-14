<?php
// usermgt.php - User management
include 'auth.php';
require_role('admin');
include 'head.php';
include 'db.php';

// Get total user count
$total_users_res = $conn->query('SELECT COUNT(*) as total FROM users');
$total_users = $total_users_res->fetch_assoc()['total'];

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
$res = $conn->query('SELECT id, name FROM teams ORDER BY name');
while ($row = $res->fetch_assoc()) {
    $teams[$row['id']] = $row['name'];
}

// List users
$res = $conn->query('SELECT u.id, u.username, u.role, u.team_id, u.frozen, t.name AS team FROM users u LEFT JOIN teams t ON u.team_id = t.id ORDER BY u.username');
$users = $res->fetch_all(MYSQLI_ASSOC);
?>

<!-- Page-Specific CSS -->
<link rel="stylesheet" href="css/usermgt.css">

<!-- Main Content Layout -->
<div class="main-content-layout">
    <!-- Left Content Area -->
    <div class="content-left">
        <div class="usermgt-container">
            <!-- Page Header -->
            <div class="usermgt-header">
                <h2 class="usermgt-title">Manage Users</h2>
                <span class="usermgt-counter"><?= $total_users ?> total users</span>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <!-- Add User Form -->
            <div class="form-section">
                <h3><i class="fa fa-plus" style="color: var(--primary)"></i> Add New User</h3>
                <form method="post" action="?action=add">
                    <?= csrf_input() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="member">Member</option>
                                <option value="teamlead">Team Lead</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="team_id">Team</label>
                            <select name="team_id" id="team_id" class="form-control">
                                <option value="0">No Team</option>
                                <?php foreach ($teams as $id => $name): ?>
                                    <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add User
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="form-section">
                <h3><i class="fa fa-users" style="color: var(--primary)"></i> All Users</h3>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Team</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td>
                                <span class="user-status <?= $u['role'] === 'admin' ? 'active' : ($u['role'] === 'teamlead' ? 'active' : 'active') ?>">
                                    <?= ucfirst($u['role']) ?>
                                </span>
                            </td>
                            <td><?= $u['team'] ?: 'None' ?></td>
                            <td>
                                <span class="user-status <?= $u['frozen'] ? 'frozen' : 'active' ?>">
                                    <i class="fa <?= $u['frozen'] ? 'fa-snowflake' : 'fa-check' ?>"></i>
                                    <?= $u['frozen'] ? 'Frozen' : 'Active' ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?action=edit&id=<?= $u['id'] ?>" class="action-btn edit" title="Edit User">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="?action=freeze&id=<?= $u['id'] ?>" class="action-btn freeze" title="Freeze User">
                                        <i class="fa fa-snowflake"></i>
                                    </a>
                                    <a href="?action=delete&id=<?= $u['id'] ?>" class="action-btn delete" title="Delete User">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($action === 'edit' && $user_id):
                $res = $conn->query('SELECT * FROM users WHERE id=' . $user_id);
                $edit = $res->fetch_assoc();
            ?>
            <!-- Edit User Form -->
            <div class="form-section">
                <h3><i class="fa fa-edit" style="color: var(--primary)"></i> Edit User: <?= htmlspecialchars($edit['username']) ?></h3>
                <form method="post" action="?action=edit&id=<?= $user_id ?>">
                    <?= csrf_input() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_role">Role</label>
                            <select name="role" id="edit_role" class="form-control" required>
                                <option value="member" <?= $edit['role'] === 'member' ? 'selected' : '' ?>>Member</option>
                                <option value="teamlead" <?= $edit['role'] === 'teamlead' ? 'selected' : '' ?>>Team Lead</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_team_id">Team</label>
                            <select name="team_id" id="edit_team_id" class="form-control">
                                <option value="0">No Team</option>
                                <?php foreach ($teams as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= $edit['team_id'] == $id ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="frozen" id="frozen" value="1" <?= $edit['frozen'] ? 'checked' : '' ?>>
                            <label for="frozen">Frozen (user cannot log in)</label>
                        </div>
                    </div>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Save Changes
                        </button>
                        <a href="usermgt.php" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Right Navigation Panel -->
    <div class="content-right">
        <?php include 'right-nav.php'; ?>
    </div>
</div>

<!-- Page-Specific JavaScript -->
<script src="js/usermgt.js"></script>

<?php include 'foot.php'; ?>
