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
    <link rel="stylesheet" href="css/right-nav.css">
    
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
        
        </nav>
    
    <!-- Global JavaScript -->
    <script src="js/main.js"></script>
    

    <?php
}
