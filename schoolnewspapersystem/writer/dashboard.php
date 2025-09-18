<?php
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/category.php";

if ($_SESSION['role'] !== 'writer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize Category object
$category = new Category($pdo);

// Fetch all articles 
$stmt = $pdo->prepare("
    SELECT a.*, u.username as author_name 
    FROM articles a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC
");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch my shared articles
$stmt = $pdo->prepare("
    SELECT a.*, u.username as author_name 
    FROM shared_articles s
    JOIN articles a ON s.article_id = a.id
    JOIN users u ON a.author_id = u.id
    WHERE s.user_id = ?
");
$stmt->execute([$user_id]);
$shared_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// get all articles (everyone)
$stmt = $pdo->prepare("
    SELECT a.*, u.username AS author_name 
    FROM articles a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC
");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// notifications for this user 
$notifStmt = $pdo->prepare("
    SELECT id, message, type, is_read, created_at 
    FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$notifStmt->execute([$user_id]);
$notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
if (!$notifications) { $notifications = []; } 

// which articles are already shared with me?
$sharedStmt = $pdo->prepare("SELECT article_id FROM shared_articles WHERE user_id = ?");
$sharedStmt->execute([$user_id]);
$sharedRows = $sharedStmt->fetchAll(PDO::FETCH_ASSOC);
$sharedWithMe = array_column($sharedRows, 'article_id');

// which articles do i already have a pending request on?
$pendingStmt = $pdo->prepare("
    SELECT article_id 
    FROM edit_requests 
    WHERE requester_id = ? AND status = 'pending'
");
$pendingStmt->execute([$user_id]);
$pendingRows = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
$pendingRequests = array_column($pendingRows, 'article_id');

$myArticles = [];
$otherArticles = [];

foreach ($articles as $a) {
    if ((int)$a['author_id'] === (int)$user_id) {
        $myArticles[] = $a;
    } else {
        $otherArticles[] = $a;
    }
}

// Pre-fetch categories for all articles to avoid N+1 query problem
$articleCategories = [];
$allArticleIds = array_merge(array_column($myArticles, 'id'), array_column($otherArticles, 'id'));

if (!empty($allArticleIds)) {
    $placeholders = implode(',', array_fill(0, count($allArticleIds), '?'));
    $categoryStmt = $pdo->prepare("
        SELECT ac.article_id, c.id, c.name 
        FROM article_categories ac
        JOIN categories c ON ac.category_id = c.id
        WHERE ac.article_id IN ($placeholders)
        ORDER BY ac.article_id, c.name
    ");
    $categoryStmt->execute($allArticleIds);
    
    while ($row = $categoryStmt->fetch(PDO::FETCH_ASSOC)) {
        $articleCategories[$row['article_id']][] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Writer Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .article-categories {
            margin: 8px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .category-badge {
            background: linear-gradient(135deg, #8a2be2, #6a11cb);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .category-badge.primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            font-weight: 600;
        }
        
        .no-categories {
            color: #666;
            font-style: italic;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h3>Writer Panel</h3>
            <ul>
                <li><a href="dashboard.php" class="active">My Articles</a></li>
                <li><a href="create_article.php">Create New Article</a></li>
                <li><a href="shared_articles.php">Shared Articles</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
        <h2>Dashboard</h2>

        <!-- My Articles -->
        <h3>My Articles</h3>
        <div class="article-grid">
            <?php if (empty($myArticles)): ?>
                <div class="notification info"><p>You haven’t created any articles yet.</p></div>
            <?php else: ?>
                <?php foreach ($myArticles as $article): ?>
                    <div class="article-card">
                       <?php if (!empty($article['image_path'])): ?>
                            <div class="article-image">
                                <img src="../<?= htmlspecialchars($article['image_path']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                            </div>
                        <?php endif; ?>

                        <div class="article-content">
                        <h3><?= htmlspecialchars($article['title']) ?></h3>
                        
                        <!-- Display Categories -->
                        <div class="article-categories">
                            <?php 
                            $hasCategories = false;
                            if (isset($articleCategories[$article['id']])): 
                                $hasCategories = true;
                                foreach ($articleCategories[$article['id']] as $cat): 
                            ?>
                                <span class="category-badge <?= ($cat['id'] == $article['category_id']) ? 'primary' : '' ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                            <?php 
                                endforeach;
                            endif;
                            
                            // Show primary category if it exists but isn't in article_categories table yet
                            if ($article['category_id'] && !$hasCategories): 
                                $primaryCatStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                                $primaryCatStmt->execute([$article['category_id']]);
                                $primaryCat = $primaryCatStmt->fetch(PDO::FETCH_ASSOC);
                                if ($primaryCat):
                            ?>
                                <span class="category-badge primary">
                                    <?= htmlspecialchars($primaryCat['name']) ?>
                                </span>
                                <?php $hasCategories = true; ?>
                            <?php 
                                endif;
                            endif;
                            
                            if (!$hasCategories): 
                            ?>
                                <span class="no-categories">No categories assigned</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="article-footer">
                            <div class="left">
                                <p>Status: <span class="status-badge"><?= htmlspecialchars($article['status']) ?></span></p>
                                <p><small><?= date("M j, Y", strtotime($article['created_at'])) ?></small></p>
                            </div>
                            <div class="right">
                                <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn">Edit</a>
                            </div>
                        </div>
                    </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Other Writers' Articles -->
        <h3>Articles by Other Writers</h3>
        <div class="article-grid">
            <?php if (empty($otherArticles)): ?>
                <div class="notification info"><p>No articles from other writers are available.</p></div>
            <?php else: ?>
                <?php foreach ($otherArticles as $article): ?>
                    <div class="article-card">
                       <?php if (!empty($article['image_path'])): ?>
                            <div class="article-image">
                                <img src="<?= htmlspecialchars($article['image_path']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                            </div>
                        <?php endif; ?>

                        <div class="article-content">
                            <h3><?= htmlspecialchars($article['title']) ?></h3>
                            
                            <!-- Display Categories for Other Articles -->
                            <div class="article-categories">
                                <?php 
                                $hasCategories = false;
                                if (isset($articleCategories[$article['id']])): 
                                    $hasCategories = true;
                                    foreach ($articleCategories[$article['id']] as $cat): 
                                ?>
                                    <span class="category-badge <?= ($cat['id'] == $article['category_id']) ? 'primary' : '' ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </span>
                                <?php 
                                    endforeach;
                                endif;
                                
                                // Show primary category if it exists but isn't in article_categories table yet
                                if ($article['category_id'] && !$hasCategories): 
                                    $primaryCatStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                                    $primaryCatStmt->execute([$article['category_id']]);
                                    $primaryCat = $primaryCatStmt->fetch(PDO::FETCH_ASSOC);
                                    if ($primaryCat):
                                ?>
                                    <span class="category-badge primary">
                                        <?= htmlspecialchars($primaryCat['name']) ?>
                                    </span>
                                    <?php $hasCategories = true; ?>
                                <?php 
                                    endif;
                                endif;
                                
                                if (!$hasCategories): 
                                ?>
                                    <span class="no-categories">No categories assigned</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="article-footer">
                                <div class="left">
                                    <p>Status: <span class="status-badge"><?= htmlspecialchars($article['status']) ?></span></p>
                                    <p><small><?= date("M j, Y", strtotime($article['created_at'])) ?></small></p>
                                </div>
                            </div>

                            <div class="actions">
                                <?php if (in_array($article['id'], $sharedWithMe, true)): ?>
                                    <a href="shared_articles.php" class="btn">Open in Shared</a>
                                <?php elseif (in_array($article['id'], $pendingRequests, true)): ?>
                                    <span class="btn disabled">Request Pending</span>
                                <?php else: ?>
                                    <a href="request_access.php?article_id=<?= $article['id'] ?>" class="btn">Request Access</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Notifications -->
        <h3>Notifications</h3>
        <?php if (empty($notifications)): ?>
            <div class="notification info"><p>You don’t have any notifications yet.</p></div>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <div class="notification <?= htmlspecialchars($n['type']) ?>">
                    <p><?= htmlspecialchars($n['message']) ?></p>
                    <small><?= date("M j, Y g:i A", strtotime($n['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('redirect_fix')) {
                const notificationContainer = document.querySelector('.notifications');
                const fixNotification = document.createElement('div');
                fixNotification.className = 'notification success';
                fixNotification.innerHTML = `
                    <p>The redirect issue has been resolved. Please log in again if needed.</p>
                    <small>${new Date().toLocaleString()}</small>
                `;
                notificationContainer.prepend(fixNotification);
            }
            document.querySelector('a[href="../logout.php"]').addEventListener('click', function(e) {
                e.preventDefault();
                alert('Logging out... If you continue to experience redirect issues, please clear your browser cookies.');
                window.location.href = '../logout.php';
            });
        });
    </script>
</body>
</html>
