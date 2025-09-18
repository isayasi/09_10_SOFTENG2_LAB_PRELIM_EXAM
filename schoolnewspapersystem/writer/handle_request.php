<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'writer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'], $_GET['action'])) {
    header("Location: notifications.php?message=Invalid request&type=danger");
    exit();
}

$request_id = (int)$_GET['id'];
$action = $_GET['action'];

// Pull request with article and requester details
$stmt = $pdo->prepare("
    SELECT r.*, a.author_id, a.title, u.username AS requester_name
    FROM edit_requests r
    JOIN articles a ON r.article_id = a.id
    JOIN users u ON r.requester_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request || (int)$request['author_id'] !== (int)$user_id) {
    header("Location: notifications.php?message=Unauthorized&type=danger");
    exit();
}

if ($request['status'] !== 'pending') {
    header("Location: notifications.php?message=Already processed&type=warning");
    exit();
}

if ($action === 'approve') {
    // Grant shared access
    $pdo->prepare("
        INSERT IGNORE INTO shared_articles (article_id, user_id)
        VALUES (?, ?)
    ")->execute([$request['article_id'], $request['requester_id']]);

    // Update request status
    $pdo->prepare("
        UPDATE edit_requests 
        SET status = 'approved', responded_at = NOW() 
        WHERE id = ?
    ")->execute([$request_id]);

    // Notify requester
    $pdo->prepare("
        INSERT INTO notifications (user_id, message, type)
        VALUES (?, ?, 'info')
    ")->execute([
        $request['requester_id'],
        "Your request for '{$request['title']}' has been approved"
    ]);

} elseif ($action === 'reject') {
    // Update request status
    $pdo->prepare("
        UPDATE edit_requests 
        SET status = 'rejected', responded_at = NOW() 
        WHERE id = ?
    ")->execute([$request_id]);

    // Notify requester
    $pdo->prepare("
        INSERT INTO notifications (user_id, message, type)
        VALUES (?, ?, 'danger')
    ")->execute([
        $request['requester_id'],
        "Your request for '{$request['title']}' has been rejected"
    ]);
}

header("Location: notifications.php?message=Request processed&type=success");
exit();
