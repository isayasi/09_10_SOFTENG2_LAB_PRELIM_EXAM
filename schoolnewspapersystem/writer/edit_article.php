<?php
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/category.php";

if ($_SESSION['role'] !== 'writer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: dashboard.php?message=No article specified&type=danger");
    exit();
}

$article_id = $_GET['id'];

// Initialize Category object
$category = new Category($pdo);

// Get all categories for dropdown
$categories = $category->read();

// Get article with categories
$stmt = $pdo->prepare("
    SELECT a.* 
    FROM articles a
    WHERE a.id = :id AND (
        a.author_id = :uid
        OR EXISTS (
            SELECT 1 FROM shared_articles s 
            WHERE s.article_id = a.id AND s.user_id = :uid
        )
    )
");
$stmt->execute(['id' => $article_id, 'uid' => $user_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header("Location: dashboard.php?message=You don't have access to this article&type=danger");
    exit();
}

// Get current article categories
$article_categories = $category->getArticleCategories($article_id);
$current_categories = [];
while ($cat = $article_categories->fetch(PDO::FETCH_ASSOC)) {
    $current_categories[] = $cat['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $status = $_POST['status'];
    $category_id = $_POST['category_id'] ?? null;
    $additional_categories = $_POST['additional_categories'] ?? [];

    // Update article
    $updateStmt = $pdo->prepare("
        UPDATE articles 
        SET title = ?, content = ?, status = ?, category_id = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$title, $content, $status, $category_id, $article_id]);

    // Update categories
    // First remove all existing categories
    $deleteStmt = $pdo->prepare("DELETE FROM article_categories WHERE article_id = ?");
    $deleteStmt->execute([$article_id]);

    // Add primary category if selected
    if ($category_id) {
        $category->addToArticle($article_id, $category_id);
    }

    // Add additional categories
    foreach ($additional_categories as $add_category_id) {
        $category->addToArticle($article_id, $add_category_id);
    }

    header("Location: shared_articles.php?message=Article updated successfully&type=success");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Article</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    <style>
        .edit-form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 1rem;
            padding: 10px 15px;
            border-radius: 8px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: var(--secondary);
        }

        .edit-form label {
            display: block;
            font-weight: bold;
            margin: 1rem 0 0.5rem;
            color: var(--primary);
        }

        .edit-form input[type="text"],
        .edit-form select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            outline: none;
            transition: border 0.3s ease, box-shadow 0.3s ease;
        }

        .edit-form input[type="text"]:focus,
        .edit-form select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 8px rgba(255,158,27,0.3);
        }

        .save-btn {
            margin-top: 1.5rem;
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(74, 111, 165, 0.3);
        }

        .save-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(74, 111, 165, 0.4);
        }

        h2 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 1.5rem;
            border-bottom: 3px dotted var(--secondary);
            padding-bottom: 10px;
        }

        .checkbox-group {
            margin: 5px 0;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 5px;
            border-left: 3px solid var(--primary);
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            cursor: pointer;
            margin: 0;
            font-weight: normal;
            color: #333;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
        }

        .current-categories {
            background: #e8f5e8;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 3px solid #4caf50;
        }

        .current-categories h4 {
            margin: 0 0 8px 0;
            color: #2e7d32;
        }

        .category-tag {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin: 2px;
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
            <div class="edit-form">
                <a href="dashboard.php" class="back-btn">
                    <i class="fa fa-arrow-left"></i> Back
                </a>

                <h2>Edit Article</h2>

                <!-- Display current categories -->
                <?php if (!empty($current_categories)): ?>
                <div class="current-categories">
                    <h4>Current Categories:</h4>
                    <?php 
                    $article_categories->execute(); // Reset pointer
                    while ($cat = $article_categories->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                    <span class="category-tag"><?php echo htmlspecialchars($cat['name']); ?></span>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <label>Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($article['title']) ?>" required>

                    <label>Primary Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">Select a Primary Category</option>
                        <?php 
                        $categories->execute(); // Reset pointer
                        while ($cat = $categories->fetch(PDO::FETCH_ASSOC)): 
                            $selected = ($article['category_id'] == $cat['id']) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>

                    <label>Additional Categories (Optional)</label>
                    <?php 
                    $categories->execute(); // Reset pointer
                    while ($cat = $categories->fetch(PDO::FETCH_ASSOC)): 
                        $checked = in_array($cat['id'], $current_categories) ? 'checked' : '';
                        // Don’t show primary category in additional categories
                        if ($article['category_id'] != $cat['id']):
                    ?>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="additional_categories[]" 
                                   value="<?php echo $cat['id']; ?>" <?php echo $checked; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </label>
                    </div>
                    <?php 
                        endif;
                    endwhile; 
                    ?>

                    <label>Content</label>
                    <textarea id="content" name="content"><?= htmlspecialchars($article['content']) ?></textarea>

                    <label>Status</label>
                    <select name="status">
                        <option value="draft" <?= $article['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>

                    <button type="submit" class="save-btn">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>

    <script>
      $(document).ready(function() {
        $('#content').summernote({
          placeholder: 'Write your article here...',
          tabsize: 2,
          height: 400
        });
      });
    </script>
</body>
</html>
