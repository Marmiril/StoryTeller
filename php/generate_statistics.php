<?php
require_once __DIR__ . '/../includes/db_connect.php';

// Verificar que llega el ID de la historia
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de historia no válido.");
}

$story_id = (int) $_GET['id'];
$db = connectDB();
if (!$db) die("Error de conexión.");

// Comprobar que la historia ya está finalizada
$stmt = $db->prepare("SELECT full_text, finished_at FROM stories WHERE id = ?");
$stmt->bind_param("i", $story_id);
$stmt->execute();
$stmt->bind_result($full_text, $finished_at);
$stmt->fetch();
$stmt->close();

if (!$finished_at) {
    die("La historia aún no ha sido finalizada.");
}

// Obtener IDs únicos de los colaboradores
$stmt = $db->prepare("SELECT DISTINCT user_id FROM collaborations WHERE story_id = ?");
$stmt->bind_param("i", $story_id);
$stmt->execute();
$result = $stmt->get_result();

$user_ids = [];
while ($row = $result->fetch_assoc()) {
    $user_ids[] = $row['user_id'];
}
$stmt->close();

if (count($user_ids) === 0) {
    die("No hay colaboradores en esta historia.");
}

// 🔧 Consulta dinámica con IN (?, ?, ?, ...)
$placeholders = implode(',', array_fill(0, count($user_ids), '?'));
$types = str_repeat('i', count($user_ids));

$sql = "SELECT age, height, weight, favorite_number, favorite_color, gender
        FROM user_preferences
        WHERE user_id IN ($placeholders)";
$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$user_ids);
$stmt->execute();
$result = $stmt->get_result();

// Agregadores
$total = 0;
$sum_age = $sum_height = $sum_weight = $sum_number = 0;
$colors = [];
$gender_count = ['M' => 0, 'F' => 0];

// Recorrer resultados solo si son válidos
while ($row = $result->fetch_assoc()) {
    $valid = true;
    foreach (['age', 'height', 'weight', 'favorite_number', 'favorite_color', 'gender'] as $field) {
        if (!isset($row[$field])) {
            $valid = false;
            break;
        }
    }
    if (!$valid) continue;

    $total++;
    $sum_age += $row['age'];
    $sum_height += $row['height'];
    $sum_weight += $row['weight'];
    $sum_number += $row['favorite_number'];

    $color = $row['favorite_color'];
    if (!isset($colors[$color])) {
        $colors[$color] = 0;
    }
    $colors[$color]++;

    $gender = $row['gender'];
    if (isset($gender_count[$gender])) {
        $gender_count[$gender]++;
    }
}
$stmt->close();

// Calcular promedios solo si hay datos válidos
$avg_age = $avg_height = $avg_weight = $avg_number = 0;
$most_common_color = 'N/A';
$gender_json = json_encode($gender_count);

if ($total > 0) {
    $avg_age = $sum_age / $total;
    $avg_height = $sum_height / $total;
    $avg_weight = $sum_weight / $total;
    $avg_number = $sum_number / $total;
    $most_common_color = array_search(max($colors), $colors);
}

// Contar palabras del cuento completo
$total_words = str_word_count(strip_tags($full_text));

// Guardar estadísticas en la base de datos
$stmt = $db->prepare("REPLACE INTO story_statistics 
    (story_id, total_collaborators, average_age, average_height, average_weight, average_favorite_number, most_common_color, gender_distribution, completion_date, total_words)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");

$stmt->bind_param(
    "idddddssi",
    $story_id,
    $total,
    $avg_age,
    $avg_height,
    $avg_weight,
    $avg_number,
    $most_common_color,
    $gender_json,
    $total_words
);

$stmt->execute();
$stmt->close();
if (!defined('IN_SAVE_COLLABORATION')) {
    $db->close();
}

?>
