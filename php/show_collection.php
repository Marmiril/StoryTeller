<?php
require_once '../includes/db_connect.php';
require_once '../includes/check_session.php';

$db = connectDB();
$userId = $_SESSION['user_id'] ?? null;
$storyId = $_GET['id'] ?? null;

if ($userId && $storyId) {
    $stmt = $db->prepare("INSERT IGNORE INTO story_views (user_id, story_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $storyId);
    $stmt->execute();
    $stmt->close();
}


require_once '../views/collection.php';
