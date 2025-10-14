<?php
// login.php - Login form
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
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Task Tracker</title>
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="css/main.css?v=2">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 2rem;
        }
        
        .login-container {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .login-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .login-title i {
            color: var(--primary);
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-group {
            text-align: left;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background: var(--white);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 128, 128, 0.1);
        }
        
        .btn-login {
            background: var(--primary);
            color: var(--white);
            padding: 0.75rem 2rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .error-message {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            padding: 1rem;
            border-radius: var(--radius-md);
            border: 1px solid rgba(220, 53, 69, 0.2);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .login-links {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .login-link {
            color: var(--gray-600);
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .login-link:hover {
            color: var(--primary);
            text-decoration: underline;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1100;
        }
        
        .modal-content {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: var(--shadow-lg);
        }
        
        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1rem;
        }
        
        .modal-message {
            color: var(--gray-600);
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        
        .btn-modal {
            background: var(--primary);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-modal:hover {
            background: var(--primary-dark);
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="login-title">
            <i class="fa fa-tasks"></i>
            Login to TaskTrack
        </h1>
        
        <?php if ($error): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fa fa-sign-in-alt"></i>
                Login
            </button>
        </form>
        
        <div class="login-links">
            <a href="#" class="login-link" onclick="showContactModal('registration')">Registration</a>
            <a href="#" class="login-link" onclick="showContactModal('forgot')">Forgot Password</a>
        </div>
    </div>
    
    <!-- Contact Administrator Modal -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">Contact Administrator</h3>
            <p class="modal-message" id="modalMessage">
                Please contact the Administrator (Jac) for assistance.
            </p>
            <button class="btn-modal" onclick="closeContactModal()">OK</button>
        </div>
    </div>
    
    <script>
        function showContactModal(type) {
            const modal = document.getElementById('contactModal');
            const message = document.getElementById('modalMessage');
            
            if (type === 'registration') {
                message.textContent = 'Please contact the Administrator (Jac) to create a new account.';
            } else if (type === 'forgot') {
                message.textContent = 'Please contact the Administrator (Jac) to reset your password.';
            }
            
            modal.style.display = 'flex';
        }
        
        function closeContactModal() {
            document.getElementById('contactModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('contactModal');
            if (event.target === modal) {
                closeContactModal();
            }
        });
    </script>
</body>
</html>
