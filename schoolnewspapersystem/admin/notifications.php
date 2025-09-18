<?php
session_start();
require_once __DIR__ . "/../config/database.php";

// Redirect non-admin users
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch all notifications with related user info
$stmt = $pdo->prepare("
    SELECT n.*, u.username AS user_name 
    FROM notifications n 
    JOIN users u ON n.user_id = u.id 
    ORDER BY n.created_at DESC
");
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<head>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<div class="dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <ul>
            <li><a href="dashboard.php">All Articles</a></li>
            <li><a href="notifications.php" class="active">Notifications</a></li>
            <li><a href="manage_categories.php">Manage Categories</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="dashboard-content">
        <h2>System Notifications</h2>
        
        <div class="notifications-list">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification <?php echo htmlspecialchars($notification['type']); ?>">
                        <div class="notification-header">
                            <strong><?php echo htmlspecialchars($notification['user_name']); ?></strong>
                            <span class="notification-date">
                                <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                            </span>
                        </div>
                        <div class="notification-body">
                            <?php echo htmlspecialchars($notification['message']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No notifications found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
