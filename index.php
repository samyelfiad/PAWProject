<?php
require 'includes/db.php';
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') header("Location: admin_dashboard.php");
    else if ($_SESSION['role'] == 'professor') header("Location: prof_dashboard.php");
    else header("Location: student_dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = "Please enter both email and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Success: Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];

                // Redirect based on role
                if ($user['role'] == 'admin') header("Location: admin_dashboard.php");
                else if ($user['role'] == 'professor') header("Location: prof_dashboard.php");
                else header("Location: student_dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error. Please try again later.";
        }
    }
}

// Don't include header for login page - we'll create a custom header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Algiers University Attendance System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .login-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            padding: 3rem 2rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .login-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .demo-credentials {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .demo-credentials h4 {
            color: var(--primary-color);
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .demo-item {
            background: #f9f9f9;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
        }

        .demo-item strong {
            color: var(--primary-color);
        }

        .demo-item small {
            color: #666;
            display: block;
            margin-top: 0.25rem;
        }

        .footer-text {
            text-align: center;
            color: #999;
            font-size: 0.85rem;
            margin-top: 2rem;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem 1.5rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h1>
            <i class="fas fa-graduation-cap"></i> AUA Attendance
        </h1>
        <p>Algiers University Attendance Management System</p>
    </div>

    <?php if ($error): ?>
        <div class="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">
                <i class="fas fa-envelope"></i> Email Address
            </label>
            <input type="email" id="email" name="email" class="form-control" 
                   placeholder="user@univ-alger.dz" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">
                <i class="fas fa-lock"></i> Password
            </label>
            <input type="password" id="password" name="password" class="form-control" 
                   placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
    </form>

    <!-- Demo Credentials -->
    <div class="demo-credentials">
        <h4><i class="fas fa-info-circle"></i> Demo Credentials</h4>
        
        <div class="demo-item">
            <strong><i class="fas fa-user-shield"></i> Administrator</strong>
            <small>Email: admin@univ-alger.dz</small>
            <small>Password: admin123</small>
        </div>

        <div class="demo-item">
            <strong><i class="fas fa-chalkboard-user"></i> Professor</strong>
            <small>Use your assigned credentials</small>
        </div>

        <div class="demo-item">
            <strong><i class="fas fa-user-graduate"></i> Student</strong>
            <small>Use your assigned credentials</small>
        </div>
    </div>

    <p class="footer-text">
        &copy; 2024 Advanced Web Programming Project | All Rights Reserved
    </p>
</div>

</body>
</html>