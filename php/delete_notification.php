<?php
require_once '../includes/check_session.php';
require_once '../includes/db_connect.php';

if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['notification_id'])) {
    header("Location: ../php/profile.php");
    exit;
}

$notificationId = intval($_POST['notification_id']);
$userId = $_SESSION['user_id'];

$db = connectDB();

// Solo eliminar si pertenece al usuario logueado
$stmt = $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notificationId, $userId);
$stmt->execute();
$stmt->close();

$db->close();
header("Location: ../php/profile.php");
exit;
