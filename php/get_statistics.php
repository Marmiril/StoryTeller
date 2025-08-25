<?php
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_GET['story_id']) || !is_numeric($_GET['story_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID de cuento no válido."]);
    exit;
}

$story_id = (int) $_GET['story_id'];
$db = connectDB();

$stmt = $db->prepare("SELECT total_collaborators, average_age, average_height, average_weight, average_favorite_number, most_common_color, gender_distribution, completion_date, total_words FROM story_statistics WHERE story_id = ?");
$stmt->bind_param("i", $story_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["error" => "No hay estadísticas disponibles para este cuento."]);
}

$stmt->close();
$db->close();
?>
