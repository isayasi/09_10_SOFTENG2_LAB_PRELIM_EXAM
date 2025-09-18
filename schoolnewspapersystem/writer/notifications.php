<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'writer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// mark single notification as read
if (isset($_GET['read'])) {
    $nid = (int)$_GET['read'];
    $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND user_id = ?
    ")->execute([$nid, $user_id]);

    header("Location: notifications.php");
    exit();
}

// mark all as read
if (isset($_GET['read_all'])) {
    $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE user_id = ?
    ")->execute([$user_id]);

    header("Location: notifications.php");
    exit();
}

// fetch my notifications
$notifStmt = $pdo->prepare("
    SELECT id, message, type, is_read, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$notifStmt->execute([$user_id]);
$notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

// fetch pending requests for articles I authored
$reqStmt = $pdo->prepare("
    SELECT r.id, r.article_id, r.requester_id, r.message, r.created_at,
           a.title, u.username AS requester_name
    FROM edit_requests r
    JOIN articles a ON r.article_id = a.id
    JOIN users u ON r.requester_id = u.id
    WHERE a.author_id = ? AND r.status = 'pending'
    ORDER BY r.created_at DESC
");
$reqStmt->execute([$user_id]);
$requests = $reqStmt->fetchAll(PDO::FETCH_ASSOC);

$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="dashboard">

    <div class="sidebar">
        <h3>Writer Panel</h3>
        <ul>
            <li><a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">My Articles</a></li>
            <li><a href="create_article.php" class="<?= $current === 'create_article.php' ? 'active' : '' ?>">Create New Article</a></li>
            <li><a href="shared_articles.php" class="<?= $current === 'shared_articles.php' ? 'active' : '' ?>">Shared Articles</a></li>
            <li><a href="notifications.php" class="<?= $current === 'notifications.php' ? 'active' : '' ?>">Notifications</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <?php if (isset($_GET['message'])): ?>
            <div class="notification <?= htmlspecialchars($_GET['type'] ?? 'success') ?>">
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>

        <div class="header">
            <h2>Notifications</h2>
            <a class="btn" href="?read_all=1">Mark All Read</a>
        </div>

        <h3>Access Requests for Your Articles</h3>
        <div class="article-grid">
            <?php if (empty($requests)): ?>
                <div class="notification info"><p>No pending requests</p></div>
            <?php else: ?>
                <?php foreach ($requests as $r): ?>
                    <div class="article-card">
                        <div class="article-content">
                            <h3><?= htmlspecialchars($r['title']) ?></h3>
                            <p>Requested by: <?= htmlspecialchars($r['requester_name']) ?></p>
                            <p><small><?= date("M j, Y g:i A", strtotime($r['created_at'])) ?></small></p>
                            <div class="actions">
                                <a class="btn" href="handle_request.php?id=<?= $r['id'] ?>&action=approve">Approve</a>
                                <a class="btn btn-danger" href="handle_request.php?id=<?= $r['id'] ?>&action=reject">Reject</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h3>Your Notifications</h3>
        <div class="article-grid">
            <?php if (empty($notifications)): ?>
                <div class="notification info"><p>No notifications</p></div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="article-card">
                        <div class="article-content">
                            <p class="notification <?= htmlspecialchars($n['type']) ?>">
                                <?= htmlspecialchars($n['message']) ?>
                                <br><small><?= date("M j, Y g:i A", strtotime($n['created_at'])) ?></small>
                            </p>
                            <?php if (!$n['is_read']): ?>
                                <div class="actions">
                                    <a class="btn" href="?read=<?= $n['id'] ?>">Mark Read</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>
