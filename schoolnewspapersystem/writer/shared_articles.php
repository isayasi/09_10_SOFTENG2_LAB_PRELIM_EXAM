<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if ($_SESSION['role'] !== 'writer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT a.*, u.username AS author_name 
    FROM shared_articles s
    JOIN articles a ON s.article_id = a.id
    JOIN users u ON a.author_id = u.id
    WHERE s.user_id = ?
    ORDER BY s.shared_at DESC
");
$stmt->execute([$user_id]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shared Articles</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h3>Writer Panel</h3>
            <ul>
                <li><a href="dashboard.php">My Articles</a></li>
                <li><a href="create_article.php">Create New Article</a></li>
                <li><a href="shared_articles.php" class="active">Shared Articles</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="dashboard-content">
            <h2>Shared Articles</h2>
            <div class="article-grid">
                <?php if (empty($articles)): ?>
                    <div class="notification info">
                        <p>No shared articles yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <div class="article-card">
                            <div class="article-content">
                                <h3><?= htmlspecialchars($article['title']) ?></h3>
                                <p>by: <?= htmlspecialchars($article['author_name']) ?></p>
                                <p><?= date("M j, Y", strtotime($article['created_at'])) ?></p>
                                <div class="actions">
                                    <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn">Edit</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
