<?php
// teammgt.php - Team management
include 'auth.php';
require_role('admin');
include 'head.php';
include 'db.php';

// Handle actions: add, edit, delete
$action = $_GET['action'] ?? '';
$team_id = intval($_GET['id'] ?? 0);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        require_csrf_post();
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            // Check if team name already exists
            $stmt = $conn->prepare('SELECT id FROM teams WHERE name = ?');
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $message = 'Team name already exists.';
            } else {
                $stmt = $conn->prepare('INSERT INTO teams (name) VALUES (?)');
                $stmt->bind_param('s', $name);
                if ($stmt->execute()) {
                    $message = 'Team added successfully.';
                } else {
                    $message = 'Error adding team.';
                }
            }
            $stmt->close();
        } else {
            $message = 'Team name is required.';
        }
    }
    
    if ($action === 'edit' && $team_id) {
        require_csrf_post();
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            // Check if team name already exists (excluding current team)
            $stmt = $conn->prepare('SELECT id FROM teams WHERE name = ? AND id != ?');
            $stmt->bind_param('si', $name, $team_id);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $message = 'Team name already exists.';
            } else {
                $stmt = $conn->prepare('UPDATE teams SET name = ? WHERE id = ?');
                $stmt->bind_param('si', $name, $team_id);
                if ($stmt->execute()) {
                    $message = 'Team updated successfully.';
                } else {
                    $message = 'Error updating team.';
                }
            }
            $stmt->close();
        } else {
            $message = 'Team name is required.';
        }
    }
}

if ($action === 'delete' && $team_id) {
    // Check if team has users
    $stmt = $conn->prepare('SELECT COUNT(*) as user_count FROM users WHERE team_id = ?');
    $stmt->bind_param('i', $team_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['user_count'] > 0) {
        $message = 'Cannot delete team that has users assigned. Please reassign users first.';
    } else {
        $stmt = $conn->prepare('DELETE FROM teams WHERE id = ?');
        $stmt->bind_param('i', $team_id);
        if ($stmt->execute()) {
            $message = 'Team deleted successfully.';
        } else {
            $message = 'Error deleting team.';
        }
        $stmt->close();
    }
}

// Get all teams
$teams = [];
$res = $conn->query('SELECT t.*, COUNT(u.id) as user_count FROM teams t LEFT JOIN users u ON t.id = u.team_id GROUP BY t.id ORDER BY t.name');
while ($row = $res->fetch_assoc()) {
    $teams[] = $row;
}

?>
<div class="container">
    <h2><i class="fa fa-users" style="color:#008080"></i> Manage Teams</h2>
    
    <?php if ($message): ?>
        <div style="margin-bottom: 1em; padding: 0.5em; background: <?php echo strpos($message, 'Error') !== false ? '#ffcccc' : '#ccffcc'; ?>; border: 1px solid #ccc;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Add Team Form -->
    <form method="post" action="?action=add" style="margin-bottom: 2em; padding: 1em; background: #f5f5f5; border-radius: 4px;">
        <?php echo csrf_input(); ?>
        <h3>Add New Team</h3>
        <input type="text" name="name" placeholder="Team Name" required style="padding: 0.5em; margin-right: 0.5em;">
        <button type="submit" style="background: #28a745; color: white; border: none; padding: 0.5em 1em; border-radius: 4px;">
            <i class="fa fa-plus"></i> Add Team
        </button>
    </form>

    <!-- Teams List -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 1em;">
        <thead>
            <tr style="background: #008080; color: #fff;">
                <th style="padding: 0.75em; text-align: left;">ID</th>
                <th style="padding: 0.75em; text-align: left;">Team Name</th>
                <th style="padding: 0.75em; text-align: center;">Members</th>
                <th style="padding: 0.75em; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teams as $team): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75em;"><?php echo $team['id']; ?></td>
                <td style="padding: 0.75em;"><?php echo htmlspecialchars($team['name']); ?></td>
                <td style="padding: 0.75em; text-align: center;">
                    <?php echo $team['user_count']; ?> user(s)
                </td>
                <td style="padding: 0.75em; text-align: center;">
                    <a href="?action=edit&id=<?php echo $team['id']; ?>" style="margin: 0 0.25em;">
                        <i class="fa fa-edit" style="color: #008080;"></i> Edit
                    </a>
                    <a href="?action=delete&id=<?php echo $team['id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this team? This action cannot be undone.');"
                       style="margin: 0 0.25em;">
                        <i class="fa fa-trash" style="color: #dc3545;"></i> Delete
                    </a>
                </td>
            </tr>
            
            <!-- Edit Form (shown when editing this team) -->
            <?php if ($action === 'edit' && $team_id == $team['id']): ?>
            <tr>
                <td colspan="4" style="padding: 1em; background: #f9f9f9;">
                    <form method="post" action="?action=edit&id=<?php echo $team['id']; ?>" style="display: flex; align-items: center; gap: 0.5em;">
                        <?php echo csrf_input(); ?>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($team['name']); ?>" required style="padding: 0.5em; flex: 1;">
                        <button type="submit" style="background: #008080; color: white; border: none; padding: 0.5em 1em; border-radius: 4px;">
                            <i class="fa fa-save"></i> Save
                        </button>
                        <a href="teammgt.php" style="background: #6c757d; color: white; padding: 0.5em 1em; border-radius: 4px; text-decoration: none;">
                            Cancel
                        </a>
                    </form>
                </td>
            </tr>
            <?php endif; ?>
            
            <?php endforeach; ?>
            
            <?php if (empty($teams)): ?>
            <tr>
                <td colspan="4" style="padding: 1em; text-align: center; color: #666;">
                    No teams found. Create your first team above.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'foot.php'; ?>