<?php
/**
 * @param PDO $pdo Database connection
 * @param int $user_id User ID to notify
 * @param string $message Notification message
 * @param string $type Notification type (info, warning, danger, deletion, edit_request)
 * @return bool True on success, false on failure
 */
function createNotification($pdo, $user_id, $message, $type = 'info') {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $message, $type]);
}

/**
 * Get notifications for a user
 * @param PDO $pdo Database connection
 * @param int $user_id User ID
 * @param int $limit Number of notifications to retrieve (0 for all)
 * @return array Array of notifications
 */
function getUserNotifications($pdo, $user_id, $limit = 0) {
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    if ($limit > 0) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Mark a notification as read
 * @param PDO $pdo Database connection
 * @param int $notification_id Notification ID
 * @return bool True on success, false on failure
 */
function markNotificationAsRead($pdo, $notification_id) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ?");
    return $stmt->execute([$notification_id]);
}

/**
 * Get unread notification count for a user
 * @param PDO $pdo Database connection
 * @param int $user_id User ID
 * @return int Number of unread notifications
 */
function getUnreadNotificationCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}