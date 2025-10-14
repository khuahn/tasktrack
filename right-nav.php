<?php
// right-nav.php - Right-side navigation panel component
if (!isset($_SESSION) || !isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    return;
}

$user_role = $_SESSION['role'];
$username = $_SESSION['username'] ?? 'User';
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
    
    <?php if (in_array($user_role, ['admin', 'teamlead'])): ?>
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
    
    <?php if (in_array($user_role, ['member', 'teamlead'])): ?>
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

<!-- Search/Filter Modal -->
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
                        <option value="LOW">Low</option>
                        <option value="MID">Mid</option>
                        <option value="HIGH">High</option>
                        <option value="PRIO">Prio</option>
                        <option value="PEND">Pending</option>
                        <option value="DONE">Done</option>
                    </select>
                </div>
                
                <?php if (in_array($user_role, ['admin', 'teamlead'])): ?>
                <div class="form-group">
                    <label for="search_assignee">Assignee</label>
                    <select class="form-control" id="search_assignee" name="assignee">
                        <option value="">All</option>
                        <?php
                        // Get users for assignee dropdown
                        if (isset($conn)) {
                            $users_res = $conn->query('SELECT id, username FROM users WHERE frozen = 0 ORDER BY username');
                            while ($user_row = $users_res->fetch_assoc()):
                        ?>
                            <option value="<?= $user_row['id'] ?>"><?= htmlspecialchars($user_row['username']) ?></option>
                        <?php 
                            endwhile; 
                        }
                        ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="search_text">Text Search</label>
                    <input class="form-control" type="text" id="search_text" name="search" placeholder="Search by task name, link, or notes">
                    <small style="color: var(--gray-600); font-size: 0.8rem;">Searches task names, links, and notes content</small>
                </div>
                
                <div class="form-group">
                    <label for="assigned_start">Assigned Date Range</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input class="form-control" type="date" id="assigned_start" name="assigned_start" placeholder="Start Date">
                        <input class="form-control" type="date" id="assigned_end" name="assigned_end" placeholder="End Date">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="completed_start">Completed Date Range</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input class="form-control" type="date" id="completed_start" name="completed_start" placeholder="Start Date">
                        <input class="form-control" type="date" id="completed_end" name="completed_end" placeholder="End Date">
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
    // Load current URL parameters into form
    loadCurrentFilters();
}

function closeSearchModal() {
    document.getElementById('searchModal').style.display = 'none';
}

function loadCurrentFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    document.getElementById('search_priority').value = urlParams.get('priority') || '';
    document.getElementById('search_assignee').value = urlParams.get('assignee') || '';
    document.getElementById('search_text').value = urlParams.get('search') || '';
    document.getElementById('assigned_start').value = urlParams.get('assigned_start') || '';
    document.getElementById('assigned_end').value = urlParams.get('assigned_end') || '';
    document.getElementById('completed_start').value = urlParams.get('completed_start') || '';
    document.getElementById('completed_end').value = urlParams.get('completed_end') || '';
}

function applySearchFilters() {
    const form = document.getElementById('searchForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Add non-empty values to URL parameters
    for (const [key, value] of formData.entries()) {
        if (value.trim() !== '') {
            params.append(key, value);
        }
    }
    
    // Redirect to current page with new parameters
    const currentUrl = window.location.pathname;
    const newUrl = params.toString() ? `${currentUrl}?${params.toString()}` : currentUrl;
    window.location.href = newUrl;
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('searchModal');
    if (event.target === modal) {
        closeSearchModal();
    }
});
</script>
