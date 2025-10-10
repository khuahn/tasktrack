<?php
// head.php - Header Template
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tracker</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .navbar {
            background: #008080;
            color: #fff;
            padding: 0.5em 1em;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            min-height: 50px;
        }
        
        .nav-welcome {
            flex: 1;
            font-size: 0.9em;
        }
        
        .nav-title {
            flex: 1;
            text-align: center;
        }
        
        .nav-title a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .nav-toggle {
            flex: 1;
            text-align: right;
        }
        
        .burger-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.3em;
            cursor: pointer;
            padding: 0.3em 0.5em;
        }
        
        /* Slide-out menu */
        .nav-menu {
            position: fixed;
            top: 0;
            right: -300px;
            width: 280px;
            height: 100vh;
            background: #008080;
            transition: right 0.3s ease;
            z-index: 1000;
            box-shadow: -2px 0 10px rgba(0,0,0,0.3);
            overflow-y: auto;
        }
        
        .nav-menu.active {
            right: 0;
        }
        
        .menu-header {
            padding: 1em;
            border-bottom: 1px solid #006666;
            margin-bottom: 0.5em;
        }
        
        .menu-header h3 {
            margin: 0;
            font-size: 1.1em;
        }
        
        .menu-links {
            padding: 0 1em;
        }
        
        .menu-link {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 0.6em 0.8em;
            margin: 0.4em 0;
            background: #006666;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .menu-link.logout {
            background: #dc3545;
        }
        
        .menu-link.manage {
            background: #28a745;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        
        .overlay.active {
            display: block;
        }
        
        .close-btn {
            position: absolute;
            top: 0.5em;
            right: 0.5em;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.3em;
            cursor: pointer;
            padding: 0.3em;
        }
    </style>
</head>
<body>
<?php
// Only show navigation if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $user_role = $_SESSION['role'];
    $username = $_SESSION['username'] ?? 'User';
    ?>
    <nav class="navbar">
        <!-- Left - Welcome message -->
        <div class="nav-welcome">
            Welcome <strong><?php echo htmlspecialchars($username); ?></strong>! (<?php echo ucfirst($user_role); ?>)
        </div>
        
        <!-- Center - Task Tracker title -->
        <div class="nav-title">
            <a href="index.php">
                <i class="fa fa-tasks"></i> Task Tracker
            </a>
        </div>
        
        <!-- Right - Hamburger menu toggle -->
        <div class="nav-toggle">
            <button class="burger-btn" id="burgerBtn">
                <i class="fa fa-bars"></i>
            </button>
        </div>
    </nav>
    
    <!-- Overlay for closing menu -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Slide-out menu -->
    <div class="nav-menu" id="navMenu">
        <button class="close-btn" id="closeBtn">
            <i class="fa fa-times"></i>
        </button>
        
        <div class="menu-header">
            <h3>Navigation Menu</h3>
        </div>
        
        <div class="menu-links">
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
                    <i class="fa fa-check-circle"></i> All Completed
                </a>
            <?php endif; ?>
            
            <a href="logout.php" class="menu-link logout">
                <i class="fa fa-sign-out"></i> Logout
            </a>
        </div>
    </div>
    
    <script>
        const burgerBtn = document.getElementById('burgerBtn');
        const closeBtn = document.getElementById('closeBtn');
        const navMenu = document.getElementById('navMenu');
        const overlay = document.getElementById('overlay');
        
        // Open menu
        burgerBtn.addEventListener('click', function() {
            navMenu.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        // Close menu
        function closeMenu() {
            navMenu.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
        
        // Close menu when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMenu();
            }
        });
        
        // Close menu when clicking on a menu link
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', closeMenu);
        });
    </script>
    <?php
}
?>
<script src="main.js"></script>