<?php
// Session security
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Algiers University - Attendance Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --light-gray: #ecf0f1;
            --dark-gray: #34495e;
            --border-radius: 8px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            color: #333;
            line-height: 1.6;
        }

        /* Navigation */
        nav {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-menu {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .navbar-menu a, .navbar-menu button {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background 0.3s ease;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .navbar-menu a:hover, .navbar-menu button:hover {
            background: rgba(255,255,255,0.2);
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-user-info {
            text-align: right;
            color: white;
        }

        .navbar-user-name {
            font-weight: bold;
            font-size: 0.95rem;
        }

        .navbar-user-role {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .logout-btn {
            background: var(--danger-color);
            padding: 0.5rem 1rem;
        }

        .logout-btn:hover {
            background: #c0392b !important;
        }

        /* Main Content */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .card h2, .card h3, .card h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .btn-primary {
            background: var(--secondary-color);
        }

        .btn-success {
            background: var(--success-color);
        }

        .btn-success:hover {
            background: #229954;
        }

        .btn-danger {
            background: var(--danger-color);
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-warning {
            background: var(--warning-color);
        }

        .btn-warning:hover {
            background: #d68910;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-gray);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
        }

        .data-table thead {
            background: var(--light-gray);
            border-bottom: 2px solid var(--secondary-color);
        }

        .data-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--primary-color);
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #ecf0f1;
        }

        .data-table tbody tr:hover {
            background: #f9f9f9;
        }

        /* Grid Layouts */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success-color);
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid var(--warning-color);
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-primary {
            background: var(--secondary-color);
            color: white;
        }

        .badge-success {
            background: var(--success-color);
            color: white;
        }

        .badge-danger {
            background: var(--danger-color);
            color: white;
        }

        .badge-warning {
            background: var(--warning-color);
            color: white;
        }

        .badge-info {
            background: #17a2b8;
            color: white;
        }

        .badge-light {
            background: var(--light-gray);
            color: var(--primary-color);
        }

        /* Status Badges */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-present {
            background: #d4edda;
            color: #155724;
        }

        .status-absent {
            background: #f8d7da;
            color: #721c24;
        }

        .status-justified {
            background: #fff3cd;
            color: #856404;
        }

        .status-pending {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Modal/Popups */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 2rem;
            border: 1px solid #888;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modal-header h2 {
            color: var(--primary-color);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #999;
        }

        .close-modal:hover {
            color: #000;
        }

        /* Status Options (for attendance) */
        .status-options {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .status-options label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: normal;
            margin: 0;
        }

        .badge-option {
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-option.present {
            background: #d4edda;
            color: #155724;
        }

        .badge-option.absent {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-option.justified {
            background: #fff3cd;
            color: #856404;
        }

        /* Utilities */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mt-4 { margin-top: 2rem; }

        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 1.5rem; }
        .mb-4 { margin-bottom: 2rem; }

        .p-1 { padding: 0.5rem; }
        .p-2 { padding: 1rem; }
        .p-3 { padding: 1.5rem; }
        .p-4 { padding: 2rem; }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .navbar {
                flex-wrap: wrap;
                gap: 1rem;
                padding: 1rem;
            }

            .navbar-menu {
                order: 3;
                width: 100%;
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }

            .grid-container {
                grid-template-columns: 1fr;
            }

            .grid-2, .grid-3, .grid-4 {
                grid-template-columns: 1fr !important;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .data-table {
                font-size: 0.85rem;
            }

            .data-table th, .data-table td {
                padding: 0.75rem;
            }

            .status-options {
                flex-direction: column;
            }

            .modal-content {
                width: 95%;
                margin: 30% auto;
            }
        }

        @media (max-width: 480px) {
            .card {
                padding: 1rem;
            }

            .navbar-brand {
                font-size: 1rem;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            .form-control {
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav>
    <div class="navbar">
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-graduation-cap"></i> AUA Attendance
        </a>
        
        <div class="navbar-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                    <a href="manage_courses.php"><i class="fas fa-book"></i> Courses</a>
                    <a href="manage_students.php"><i class="fas fa-users"></i> Students</a>
                <?php elseif ($_SESSION['role'] === 'professor'): ?>
                    <a href="prof_dashboard.php"><i class="fas fa-chalkboard"></i> My Courses</a>
                    <a href="attendance_summary.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <?php elseif ($_SESSION['role'] === 'student'): ?>
                    <a href="student_dashboard.php"><i class="fas fa-book"></i> My Courses</a>
                    <a href="student_dashboard.php"><i class="fas fa-clipboard-list"></i> My Attendance</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="navbar-user">
            <div class="navbar-user-info">
                <div class="navbar-user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></div>
                <div class="navbar-user-role"><?php echo ucfirst($_SESSION['role'] ?? ''); ?></div>
            </div>
            <form method="POST" action="logout.php" style="display:inline;">
                <button type="submit" class="btn logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</nav>

<!-- Main Container -->
<div class="container">
