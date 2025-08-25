<?php
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;

    // Permitir campos opcionales
    $favorite_number = $_POST['favorite_number'] !== '' ? $_POST['favorite_number'] : null;
    $favorite_color = $_POST['favorite_color'] !== '' ? $_POST['favorite_color'] : null;
    $height = $_POST['height'] !== '' ? $_POST['height'] : null;
    $weight = $_POST['weight'] !== '' ? $_POST['weight'] : null;
    $age = $_POST['age'] !== '' ? $_POST['age'] : null;
    $gender = $_POST['gender'] !== '' ? $_POST['gender'] : null;

    try {
        $db = connectDB();

        $stmt = $db->prepare("INSERT INTO user_preferences (
            user_id, favorite_number, favorite_color, height, weight, age, gender
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            favorite_number = VALUES(favorite_number),
            favorite_color = VALUES(favorite_color),
            height = VALUES(height),
            weight = VALUES(weight),
            age = VALUES(age),
            gender = VALUES(gender)");

        // bind_param no acepta nulls directamente
        $stmt->bind_param(
            "iisddis",
            $user_id,
            $favorite_number,
            $favorite_color,
            $height,
            $weight,
            $age,
            $gender
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al ejecutar la consulta.'
            ]);
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Excepción: ' . $e->getMessage()
        ]);
    } finally {
        if (isset($db)) {
            $db->close();
        }
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
