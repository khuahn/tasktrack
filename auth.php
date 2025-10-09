<?php
// auth.php - Session and authentication logic
session_start();
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
function get_user_role() {
    return $_SESSION['role'] ?? null;
}
function require_role($role) {
    require_login();
    if (get_user_role() !== $role) {
        // Redirect based on role
        if (get_user_role() === 'admin') {
            header('Location: taskmgt.php');
        } else {
            header('Location: index.php');
        }
        exit;
    }
}
function require_any_role($roles) {
    require_login();
    if (!in_array(get_user_role(), $roles)) {
        if (get_user_role() === 'admin') {
            header('Location: taskmgt.php');
        } else {
            header('Location: index.php');
        }
        exit;
    }
}
