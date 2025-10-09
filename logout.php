<?php
// logout.php - Logout logic placeholder
session_start();
session_destroy();
header('Location: login.php');
exit;
