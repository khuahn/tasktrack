<?php
// login.php - Login form
include 'head.php';
include 'db.php';
session_start();
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
                // Redirect by role
                if ($row['role'] === 'admin') {
                    header('Location: taskmgt.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
        $stmt->close();
    } else {
        $error = 'Please enter username and password.';
    }
}
?>
<div class="container">
    <form method="post" class="login-form">
        <h2><i class="fa fa-sign-in-alt" style="color:#008080"></i> Login</h2>
        <?php if ($error): ?>
            <div class="error" style="color:red; margin-bottom:1em;"><?php echo $error; ?></div>
        <?php endif; ?>
        <label for="username"><i class="fa fa-user"></i> Username</label>
        <input type="text" name="username" id="username" required autofocus>
        <label for="password"><i class="fa fa-lock"></i> Password</label>
        <input type="password" name="password" id="password" required>
        <button type="submit" style="background:#008080;color:#fff;padding:0.5em 2em;border:none;border-radius:4px;margin-top:1em;">
            <i class="fa fa-arrow-right"></i> Login
        </button>
    </form>
</div>
<?php include 'foot.php'; ?>
