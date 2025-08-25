<?php
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = connectDB();
/*
        //Validar user_id
        if(!isset($_POST['user_id']) || empty($_POST['user_id'])) {
            throw new Exception("Usuario no identificado");
        }
*/
        //Validar user_id
        if(!isset($_POST['user_id']) || empty($_POST['user_id'])) {
            throw new Exception("Usuario no identificado");
        }

        // Obtener datos del formulario
        $title = $_POST['story-title'];
        $theme = $_POST['story-theme'];
        $guide_word = $_POST['guide-word'];
        $max_steps = $_POST['story-steps'];
        $content = $_POST['story-content'];
        $user_id = $_POST['user_id'];
        $current_step = 1; // Añadido esta variable que faltaba

        // Verificar si ya existe una historia con el mismo título del mismo usuario
        $checkStory = $db->prepare("SELECT id FROM stories WHERE title = ? AND user_id = ?");
        $checkStory->bind_param("si", $title, $user_id);
        $checkStory->execute();
        $result = $checkStory->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Ya has creado un cuento con este título");
        }

        // Iniciar transacción
        $db->begin_transaction();

        //Insertar en stories.
        $stmt = $db->prepare("INSERT INTO stories (title, theme, guide_word, max_steps, current_step, user_id, created_at, full_text)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param('sssiiss', $title, $theme, $guide_word, $max_steps, $current_step, $user_id, $content);  
         
        if ($stmt->execute()) {
            $story_id = $db->insert_id;
            
            // Insertar en collaborations
            $step_number = 1;  // Crear variable para el step_number
            $stmtCollab = $db->prepare("INSERT INTO collaborations (story_id, user_id, content, step_number) VALUES (?, ?, ?, ?)");
            $stmtCollab->bind_param('iisi', $story_id, $user_id, $content, $step_number);
            
            if ($stmtCollab->execute()) {
                $db->commit();
                echo json_encode([
                    'success' => true,
                    'story_saved' => $story_id,
                    'message' => 'Historia guardada con éxito'
                ]);
                exit; // Asegurarnos de que no hay más output
            }
        }
        
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollback();
        }
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit; // Asegurarnos de que no hay más output
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}
?>