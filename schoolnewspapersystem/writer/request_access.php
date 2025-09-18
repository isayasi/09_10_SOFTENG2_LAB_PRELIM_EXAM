<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'writer') {
    header("Location: ../login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'someone';

if (!isset($_GET['article_id'])) {
    header("Location: dashboard.php?message=invalid article&type=danger");
    exit();
}

$article_id = (int) $_GET['article_id'];

// get article + author
$stmt = $pdo->prepare("SELECT id, title, author_id FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header("Location: dashboard.php?message=article not found&type=danger");
    exit();
}

// cannot request access to your own article
if ((int) $article['author_id'] === (int) $user_id) {
    header("Location: dashboard.php?message=that’s your own article&type=warning");
    exit();
}

// already shared?
$chkShared = $pdo->prepare("SELECT 1 FROM shared_articles WHERE article_id = ? AND user_id = ?");
$chkShared->execute([$article_id, $user_id]);

if ($chkShared->fetchColumn()) {
    header("Location: dashboard.php?message=you already have access&type=info");
    exit();
}

// already have a pending request?
$chkReq = $pdo->prepare("
    SELECT 1 
    FROM edit_requests 
    WHERE article_id = ? AND requester_id = ? AND status = 'pending'
");
$chkReq->execute([$article_id, $user_id]);

if ($chkReq->fetchColumn()) {
    header("Location: dashboard.php?message=request already pending&type=info");
    exit();
}

// create request
$pdo->prepare("
    INSERT INTO edit_requests (article_id, requester_id, message) 
    VALUES (?, ?, 'requesting edit access')
")->execute([$article_id, $user_id]);

// notify author
$pdo->prepare("
    INSERT INTO notifications (user_id, message, type)
    VALUES (?, ?, 'edit_request')
")->execute([
    $article['author_id'],
    "user {$username} requested edit access for '{$article['title']}'"
]);

header("Location: dashboard.php?message=request sent&type=success");
exit();
