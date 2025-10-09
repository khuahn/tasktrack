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

// CSRF protection utilities
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input() {
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function require_csrf_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $provided = $_POST['csrf_token'] ?? '';
    if (!$sessionToken || !$provided || !hash_equals($sessionToken, $provided)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
}
