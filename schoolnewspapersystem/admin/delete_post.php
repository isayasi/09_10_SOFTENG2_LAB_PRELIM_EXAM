<?php
session_start();
require_once __DIR__ . "/../config/database.php";

// Redirect non-admin users
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if article ID is provided
if (isset($_GET['id'])) {
    $article_id = $_GET['id'];

    // Fetch the article
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($article) {
        $author_id = $article['author_id'];
        $title = $article['title'];

        // Delete the article
        $deleteStmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $deleteStmt->execute([$article_id]);

        // Notify the author about deletion
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (user_id, message, type)
            VALUES (?, ?, 'deletion')
        ");
        $notifStmt->execute([
            $author_id,
            "Your article '{$title}' has been deleted by the admin."
        ]);

        // Redirect with success message
        header("Location: dashboard.php?message=Article deleted successfully&type=success");
        exit();
    }
}

// Redirect if article not found or ID missing
header("Location: dashboard.php?message=Invalid article&type=danger");
exit();
