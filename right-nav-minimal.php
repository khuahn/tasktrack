<?php
// right-nav.php - Minimal version for InfinityFree compatibility
// Only show navigation if user is logged in
if (!isset($_SESSION) || !isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    return;
}

$user_role = $_SESSION['role'];
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

<!-- Simple Search Modal -->
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
                
                <div class="form-group">
                    <label for="search_text">Text Search</label>
                    <input class="form-control" type="text" id="search_text" name="search" placeholder="Search by task name or link">
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
    var priority = document.getElementById('search_priority').value;
    var search = document.getElementById('search_text').value;
    var params = [];
    
    if (priority) params.push('priority=' + encodeURIComponent(priority));
    if (search) params.push('search=' + encodeURIComponent(search));
    
    var currentUrl = window.location.pathname;
    var newUrl = params.length > 0 ? currentUrl + '?' + params.join('&') : currentUrl;
    window.location.href = newUrl;
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    var modal = document.getElementById('searchModal');
    if (event.target === modal) {
        closeSearchModal();
    }
});
</script>
