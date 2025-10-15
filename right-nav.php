<?php
// right-nav.php - Minimal version for InfinityFree compatibility
// Only show navigation if user is logged in
if (!isset($_SESSION) || !isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    return;
}

$user_role = $_SESSION['role'];

// Preload current filter values from URL (bookmarkable)
$f_priority = $_GET['priority'] ?? '';
$f_search = $_GET['search'] ?? '';
$f_assignee = intval($_GET['assignee'] ?? 0);
$f_assigned_start = $_GET['assigned_start'] ?? '';
$f_assigned_end = $_GET['assigned_end'] ?? '';
$f_completed_start = $_GET['completed_start'] ?? '';
$f_completed_end = $_GET['completed_end'] ?? '';

// Build assignee list for Admin/Team Lead
$assigneeUsers = [];
if ($user_role === 'admin' || $user_role === 'teamlead') {
    if (!isset($conn)) { include 'db.php'; }
    if ($user_role === 'admin') {
        $res = $conn->query('SELECT id, username FROM users WHERE role IN ("teamlead","member") AND frozen=0 ORDER BY username');
    } else {
        $uid = intval($_SESSION['user_id']);
        $teamRes = $conn->query('SELECT team_id FROM users WHERE id=' . $uid);
        $teamRow = $teamRes ? $teamRes->fetch_assoc() : null;
        $teamId = intval($teamRow['team_id'] ?? 0);
        $res = $conn->query('SELECT id, username FROM users WHERE team_id=' . $teamId . ' AND role IN ("teamlead","member") AND frozen=0 ORDER BY username');
    }
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $assigneeUsers[intval($row['id'])] = $row['username'];
        }
    }
}
?>

<!-- Right-side Navigation Panel -->
<div class="right-nav-panel">
    <?php if ($user_role === 'admin'): ?>
        <a href="usermgt.php" class="nav-button manage-users">
            <i class="fa fa-users"></i>
            <span>Manage Users</span>
        </a>
    <?php endif; ?>
    
    <button class="nav-button search-filter" onclick="openSearchModal()">
        <i class="fa fa-search"></i>
        <span>Search/Filter</span>
    </button>
    
    <?php if ($user_role === 'admin' || $user_role === 'teamlead'): ?>
        <a href="taskmgt.php" class="nav-button manage-tasks">
            <i class="fa fa-tasks"></i>
            <span>Manage Tasks</span>
        </a>
    <?php endif; ?>
    
    <?php if ($user_role === 'admin'): ?>
        <a href="all_done.php" class="nav-button all-completed">
            <i class="fa fa-check-double"></i>
            <span>All Completed</span>
        </a>
    <?php endif; ?>
    
    <?php if ($user_role === 'teamlead'): ?>
        <a href="team_done.php" class="nav-button team-completed">
            <i class="fa fa-users"></i>
            <span>Team Completed</span>
        </a>
    <?php endif; ?>
    
    <?php if ($user_role === 'member' || $user_role === 'teamlead'): ?>
        <a href="user_done.php" class="nav-button user-completed">
            <i class="fa fa-check-circle"></i>
            <span>User Completed</span>
        </a>
    <?php endif; ?>
    
    <a href="logout.php" class="nav-button logout">
        <i class="fa fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>

<!-- Search / Filter Modal -->
<div id="searchModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa fa-search" style="color:#008080"></i> Search & Filter</h3>
        </div>
        <div class="modal-body">
            <form id="searchForm">
                <div class="form-group">
                    <label for="search_priority">Priority</label>
                    <select class="form-control" id="search_priority" name="priority">
                        <option value="">All</option>
                        <option value="LOW" <?= $f_priority==='LOW' ? 'selected' : '' ?>>Low</option>
                        <option value="MID" <?= $f_priority==='MID' ? 'selected' : '' ?>>Mid</option>
                        <option value="HIGH" <?= $f_priority==='HIGH' ? 'selected' : '' ?>>High</option>
                        <option value="PRIO" <?= $f_priority==='PRIO' ? 'selected' : '' ?>>Prio</option>
                        <option value="PEND" <?= $f_priority==='PEND' ? 'selected' : '' ?>>Pending</option>
                        <option value="DONE" <?= $f_priority==='DONE' ? 'selected' : '' ?>>Done</option>
                    </select>
                </div>
                
                <?php if ($user_role === 'admin' || $user_role === 'teamlead'): ?>
                <div class="form-group">
                    <label for="search_assignee">Assignee</label>
                    <select class="form-control" id="search_assignee" name="assignee">
                        <option value="">All</option>
                        <?php foreach ($assigneeUsers as $id => $name): ?>
                            <option value="<?= intval($id) ?>" <?= $f_assignee === intval($id) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="search_text">Text Search</label>
                    <input class="form-control" type="text" id="search_text" name="search" placeholder="Search by task name, link, or notes" value="<?= htmlspecialchars($f_search) ?>">
                </div>

                <div class="form-row" style="display:flex; gap: 0.5rem;">
                    <div class="form-group" style="flex:1; min-width: 140px;">
                        <label for="assigned_start">Assigned Start</label>
                        <input class="form-control" type="date" id="assigned_start" name="assigned_start" value="<?= htmlspecialchars($f_assigned_start) ?>">
                    </div>
                    <div class="form-group" style="flex:1; min-width: 140px;">
                        <label for="assigned_end">Assigned End</label>
                        <input class="form-control" type="date" id="assigned_end" name="assigned_end" value="<?= htmlspecialchars($f_assigned_end) ?>">
                    </div>
                </div>

                <div class="form-row" style="display:flex; gap: 0.5rem;">
                    <div class="form-group" style="flex:1; min-width: 140px;">
                        <label for="completed_start">Completed Start</label>
                        <input class="form-control" type="date" id="completed_start" name="completed_start" value="<?= htmlspecialchars($f_completed_start) ?>">
                    </div>
                    <div class="form-group" style="flex:1; min-width: 140px;">
                        <label for="completed_end">Completed End</label>
                        <input class="form-control" type="date" id="completed_end" name="completed_end" value="<?= htmlspecialchars($f_completed_end) ?>">
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="applySearchFilters()">
                        <i class="fa fa-check"></i> Apply
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeSearchModal()">
                        <i class="fa fa-times"></i> Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSearchModal() {
    document.getElementById('searchModal').style.display = 'flex';
}

function closeSearchModal() {
    document.getElementById('searchModal').style.display = 'none';
}

function applySearchFilters() {
    var params = new URLSearchParams(window.location.search);
    var priority = document.getElementById('search_priority')?.value || '';
    var search = document.getElementById('search_text')?.value || '';
    var assigneeEl = document.getElementById('search_assignee');
    var assignee = assigneeEl ? assigneeEl.value : '';
    var assignedStart = document.getElementById('assigned_start')?.value || '';
    var assignedEnd = document.getElementById('assigned_end')?.value || '';
    var completedStart = document.getElementById('completed_start')?.value || '';
    var completedEnd = document.getElementById('completed_end')?.value || '';

    // Set or clear params
    if (priority) params.set('priority', priority); else params.delete('priority');
    if (search) params.set('search', search); else params.delete('search');
    if (assignee) params.set('assignee', assignee); else params.delete('assignee');
    if (assignedStart) params.set('assigned_start', assignedStart); else params.delete('assigned_start');
    if (assignedEnd) params.set('assigned_end', assignedEnd); else params.delete('assigned_end');
    if (completedStart) params.set('completed_start', completedStart); else params.delete('completed_start');
    if (completedEnd) params.set('completed_end', completedEnd); else params.delete('completed_end');

    var currentUrl = window.location.pathname;
    var qs = params.toString();
    window.location.href = qs ? (currentUrl + '?' + qs) : currentUrl;
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    var modal = document.getElementById('searchModal');
    if (event.target === modal) {
        closeSearchModal();
    }
});
</script>
