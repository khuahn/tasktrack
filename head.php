<?php
// head.php - Header Template
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tracker</title>
    
    <!-- Global CSS First -->
    <link rel="stylesheet" href="css/main.css?v=2">
    
    <!-- Component-Specific CSS -->
    <link rel="stylesheet" href="css/head.css">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
<?php
// Only show navigation if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $user_role = $_SESSION['role'];
    $username = $_SESSION['username'] ?? 'User';
    ?>
    
    <!-- Modern Navigation Bar -->
    <nav class="navbar">
        <!-- Welcome Section -->
        <div class="nav-welcome">
            <i class="fa fa-user-circle"></i> Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>
        </div>
        
        <!-- Title Section -->
        <div class="nav-title">
            <a href="index.php">
                <i class="fa fa-tasks"></i> Task Tracker
            </a>
        </div>
        
        <!-- Menu Toggle -->
        <div class="nav-toggle">
            <button class="burger-btn" id="burgerBtn" aria-label="Toggle menu">
                <i class="fa fa-bars"></i>
            </button>
        </div>
    </nav>
    
    <!-- Overlay for closing menu -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Slide-out Navigation Menu -->
    <div class="nav-menu" id="navMenu">
        <button class="close-btn" id="closeBtn" aria-label="Close menu">
            <i class="fa fa-times"></i>
        </button>
        
        <div class="menu-header">
            <h3><i class="fa fa-compass"></i> Navigation</h3>
        </div>
        
        <div class="menu-links">
            <a href="#" class="menu-link" onclick="if(window.openFilterModal){openFilterModal();} return false;">
                <i class="fa fa-filter"></i> Filter/Search
            </a>
            <?php if (in_array($user_role, ['admin', 'teamlead'])): ?>
                <a href="taskmgt.php" class="menu-link manage">
                    <i class="fa fa-tasks"></i> Manage Tasks
                </a>
            <?php endif; ?>
            
            <?php if ($user_role === 'admin'): ?>
                <a href="usermgt.php" class="menu-link">
                    <i class="fa fa-users"></i> Manage Users
                </a>
            <?php endif; ?>
            
            <?php if ($user_role === 'admin'): ?>
                <a href="teammgt.php" class="menu-link">
                    <i class="fa fa-users-cog"></i> Manage Teams
                </a>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['teamlead', 'member'])): ?>
                <a href="user_done.php" class="menu-link">
                    <i class="fa fa-check-circle"></i> My Completed
                </a>
            <?php endif; ?>
            
            <?php if ($user_role === 'teamlead'): ?>
                <a href="team_done.php" class="menu-link">
                    <i class="fa fa-users"></i> Team Completed
                </a>
            <?php endif; ?>
            
            <?php if ($user_role === 'admin'): ?>
                <a href="all_done.php" class="menu-link">
                    <i class="fa fa-check-double"></i> All Completed
                </a>
            <?php endif; ?>
            
            <a href="logout.php" class="menu-link logout">
                <i class="fa fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <!-- Global JavaScript -->
    <script src="js/main.js"></script>
    
    <!-- Component-Specific JavaScript -->
    <script src="js/head.js"></script>
    
    <?php
}
