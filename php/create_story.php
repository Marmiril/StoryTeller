<?php
require_once __DIR__ . '/../includes/check_session.php';
require_once __DIR__ . '/../includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validación de sesión iniciada
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Debes iniciar sesión para guardar una historia.'
        ]);
        exit;
    }

    $title = trim($_POST['title']);
    $theme = trim($_POST['theme']);
    $steps = (int) $_POST['steps'];
    $guide_word = trim($_POST['guide_word']);
    $fragment = trim($_POST['fragment']);

    // Validaciones
    if (empty($title) || empty($theme) || empty($fragment)) {
        $errors[] = "Todos los campos excepto la palabra guía son obligatorios.";
    } elseif ($steps < 5 || $steps > 15) {
        $errors[] = "El número de pasos debe estar entre 5 y 15.";
    } elseif (str_word_count($fragment) < 150 || str_word_count($fragment) > 600) {
        $errors[] = "El fragmento debe tener entre 150 y 600 palabras.";
    }

    if (empty($errors)) {
        $db = connectDB();
        if (!$db) {
            echo json_encode([
                'success' => false,
                'message' => 'Error de conexión con la base de datos.'
            ]);
            exit;
        }

        // Comprobar si el título ya existe
        $stmt = $db->prepare("SELECT id FROM stories WHERE title = ? AND user_id = ?");
        $stmt->bind_param("si", $title, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya has creado una historia con ese título.'
            ]);
            exit;
        }
        $stmt->close();

        // Insertar historia
        $stmt = $db->prepare("INSERT INTO stories (title, theme, guide_word, max_steps, current_step, user_id, created_at)
                              VALUES (?, ?, ?, ?, 1, ?, NOW())");
        $stmt->bind_param("sssii", $title, $theme, $guide_word, $steps, $_SESSION['user_id']);
        $stmt->execute();
        $story_id = $stmt->insert_id;
        $stmt->close();

        // Insertar primera colaboración
        $stmt = $db->prepare("INSERT INTO collaborations (story_id, user_id, content, step_number)
                              VALUES (?, ?, ?, 1)");
        $stmt->bind_param("iis", $story_id, $_SESSION['user_id'], $fragment);
        $stmt->execute();
        $stmt->close();
        $db->close();

        echo json_encode([
            'success' => true,
            'story_saved' => $story_id
        ]);
        exit;
    }

    // Devolver errores
    echo json_encode([
        'success' => false,
        'message' => implode(" ", $errors)
    ]);
    exit;
}
?>
