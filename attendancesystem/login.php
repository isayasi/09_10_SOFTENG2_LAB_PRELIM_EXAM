<?php
include_once 'config/database.php';
include_once 'objects/user.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$message = '';

// Handle login form submission
if ($_POST) {
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    
    if ($user->login()) {
        session_start();
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        
        // Redirect based on role
        if ($user->role == 'admin') {
            header("Location: admin/admin_dashboard.php");
        } else {
            header("Location: student/student_dashboard.php");
        }
        exit();
    } else {
        $message = '<div class="alert alert-danger">Invalid username or password.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">

    <style>
        :root {
            --dark-bg: #121212;
            --darker-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --primary: #8a2be2;
            --primary-hover: #9b45ed;
            --text: #e0e0e0;
            --text-muted: #a0a0a0;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .card {
            background-color: var(--card-bg);
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), #6a11cb);
            border: none;
            padding: 25px 20px;
            text-align: center;
        }

        .card-header h2 {
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            background-color: #2a2a2a;
            border: 1px solid #3a3a3a;
            color: var(--text);
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            background-color: #2a2a2a;
            border-color: var(--primary);
            color: var(--text);
            box-shadow: 0 0 0 0.25rem rgba(138, 43, 226, 0.25);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
        }

        .register-link {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 500;
        }

        .register-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .brand-text {
            text-align: center;
            margin-top: 30px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .input-icon {
            position: relative;
        }

        .input-icon .form-control {
            padding-left: 40px;
        }

        .input-icon i {
            position: absolute;
            left: 15px;
            top: 12px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="card">
        <div class="card-header">
            <h2><i class="bi bi-person-circle me-2"></i>Login</h2>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-icon">
                        <i class="bi bi-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3">Login</button>
                <p class="text-center mt-4">
                    Don't have an account? <a href="register.php" class="register-link">Register here</a>
                </p>
            </form>
        </div>
    </div>

    <div class="brand-text">
        Attendance System &copy; <?php echo date('Y'); ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
