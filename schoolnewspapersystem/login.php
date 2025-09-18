<?php
session_start();
require_once __DIR__ . "/config/database.php";

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: writer/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: writer/dashboard.php');
        }
        exit();
    } else {
        $error = "Invalid username/email or password";
    }
}
?>
<head>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<div class="login-page">
    <div class="login-container">
        <div class="login-character">
            <div style="width: 120px; height: 120px; background-color: #4e7ae6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <span style="color: white; font-size: 40px;">📝</span>
            </div>
        </div>
        
        <h2>Login to School Newspaper</h2>
        
        <?php if ($error): ?>
            <div class="notification danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Login</button>
            
            <p style="text-align: center; margin-top: 20px;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </form>
    </div>
</div>
