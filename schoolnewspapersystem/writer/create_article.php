<?php
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/category.php";

// Ensure the user is a writer
if ($_SESSION['role'] !== 'writer') {
    header('Location: ../login.php');
    exit();
}

// Fetch all categories for the dropdown
$category = new Category($pdo);
$categories = $category->read();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content']; 
    $author_id = $_SESSION['user_id'];
    $category_id = $_POST['category_id'] ?? null;

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/articles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $destination = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $image_path = 'uploads/articles/' . $filename;
        }
    }

    // Insert the article with primary category
    $stmt = $pdo->prepare("INSERT INTO articles (title, content, author_id, category_id, image_path, created_at, updated_at) 
                           VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$title, $content, $author_id, $category_id, $image_path]);
    
    $article_id = $pdo->lastInsertId();
    
    // Assign additional categories if selected
    if (!empty($_POST['additional_categories'])) {
        foreach ($_POST['additional_categories'] as $add_category_id) {
            $category->addToArticle($article_id, $add_category_id);
        }
    }

    header('Location: dashboard.php?message=Article created successfully&type=success');
    exit();
}

$current = basename($_SERVER['PHP_SELF']); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Article</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/create_article.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
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
        <h2>Create New Article</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Article Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Primary Category</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select a Category</option>
                    <?php while ($cat = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Additional Categories (Optional)</label>
                <?php 
                // Reset the pointer to reuse categories
                $categories->execute();
                while ($cat = $categories->fetch(PDO::FETCH_ASSOC)): 
                ?>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="additional_categories[]" value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </label>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="form-group">
                <label>Article Content</label>
                <textarea id="content" name="content"></textarea>
            </div>

            <div class="form-group">
                <label>Featured Image (Optional)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <button type="submit">✨ Create Article</button>
        </form>
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
