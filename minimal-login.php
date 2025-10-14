<?php
// minimal-login.php - Ultra simple login test
session_start();
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = $conn->prepare('SELECT id, username, password, role FROM users WHERE username = ? AND frozen = 0 LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'User not found.';
        }
        $stmt->close();
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .form { max-width: 300px; }
        input { width: 100%; padding: 10px; margin: 5px 0; }
        button { background: #008080; color: white; padding: 10px 20px; border: none; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Minimal Login Test</h1>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="post" class="form">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    
    <hr>
    <p><a href="simple-test.php">Run Simple Test</a></p>
</body>
</html>
