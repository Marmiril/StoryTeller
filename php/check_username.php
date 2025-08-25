<?php
header('Content-Type: application/json');

// Incluir configuración con ruta correcta
include(__DIR__ . '/../config/config.php');

// Verificar que la conexión está disponible
if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo conectar a la base de datos.']);
    exit;
}

// Obtener y limpiar datos del body JSON
$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');

$response = ['exists' => false];

if (!empty($username)) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response['exists'] = true;
    }

    $stmt->close();
}

echo json_encode($response);
?>
