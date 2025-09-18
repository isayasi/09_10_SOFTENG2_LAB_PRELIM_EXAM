<?php
session_start();
require_once __DIR__ . "/../config/database.php";

// Redirect non-admin users
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch all articles with author info
$stmt = $pdo->prepare("
    SELECT a.*, u.username AS author_name 
    FROM articles a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC
");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Articles</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Admin Panel</h3>
            <div class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active">All Articles</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="manage_categories.php">Manage Categories</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="dashboard-content">
            <h2>Manage Articles</h2>

            <!-- Notification Message -->
            <?php if (isset($_GET['message'])): ?>
                <div class="notification <?php echo $_GET['type'] ?? 'success'; ?>">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Articles Grid -->
            <div class="article-grid">
                <?php foreach ($articles as $article): ?>
                    <div class="article-card">
                        <?php if (!empty($article['image_path'])): ?>
                            <div class="article-image">
                                <img src="../uploads/articles/<?php echo htmlspecialchars($article['image_path']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                            </div>
                        <?php endif; ?>

                        <div class="article-content">
                            <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                            <p>By: <?php echo htmlspecialchars($article['author_name']); ?></p>
                            <p><?php echo date('M j, Y', strtotime($article['created_at'])); ?></p>

                            <div class="actions">
                                <a href="delete_post.php?id=<?php echo $article['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this article?')">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
