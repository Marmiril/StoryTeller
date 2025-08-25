<?php
require_once '../includes/db_connect.php';
require_once '../includes/check_session.php';

// Asegurarse de que el usuario esté logueado
if (!isLoggedIn()) {
    echo json_encode(['alreadyCollaborated' => false]);
    exit;
}

// Validar story_id
if (!isset($_GET['story_id']) || !is_numeric($_GET['story_id'])) {
    echo json_encode(['alreadyCollaborated' => false]);
    exit;
}

$userId = $_SESSION['user_id'];
$storyId = (int) $_GET['story_id'];

$db = connectDB();

$stmt = $db->prepare("
    SELECT COUNT(*) FROM collaborations
    WHERE story_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $storyId, $userId);
$stmt->execute();
$stmt->bind_result($collabCount);
$stmt->fetch();
$stmt->close();
$db->close();

echo json_encode(['alreadyCollaborated' => $collabCount > 0]);
exit;
?>
