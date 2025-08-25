<?php
require_once '../includes/check_session.php';
require_once '../includes/db_connect.php';

if ($_SESSION['user_id'] != 1) {
    die("Acceso no autorizado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = connectDB();

    // Eliminar paso individual
    if (isset($_POST['fragment_id'])) {
        $fragmentId = intval($_POST['fragment_id']);

        // Obtener story_id, user_id y título antes de eliminar
        $stmt = $db->prepare("SELECT c.story_id, c.user_id, s.title
                              FROM collaborations c
                              JOIN stories s ON c.story_id = s.id
                              WHERE c.id = ?");
        $stmt->bind_param("i", $fragmentId);
        $stmt->execute();
        $stmt->bind_result($storyId, $userId, $storyTitle);
        $stmt->fetch();
        $stmt->close();
        

        // Eliminar el fragmento
        $stmt = $db->prepare("DELETE FROM collaborations WHERE id = ?");
        $stmt->bind_param("i", $fragmentId);

        if ($stmt->execute()) {
            $stmt->close();

            // Restar 1 al current_step del cuento
            $stmt = $db->prepare("UPDATE stories SET current_step = current_step - 1 WHERE id = ?");
            $stmt->bind_param("i", $storyId);
            $stmt->execute();
            $stmt->close();

            // Insertar notificación para el colaborador
            $mensaje = "Tu colaboración en el cuento \"" . $storyTitle . "\" ha sido eliminada por el administrador.";
            //DEBUG
            file_put_contents(__DIR__ . "/notificacion_debug.txt", "User: $userId | Story: $storyId | Msg: $mensaje\n", FILE_APPEND);


            $stmt = $db->prepare("INSERT INTO notifications (user_id, type, story_id, fragment_id, message) VALUES (?, 'fragment_deleted', ?, ?, ?)");
            $stmt->bind_param("iiis", $userId, $storyId, $fragmentId, $mensaje);
            $stmt->execute();

            if ($stmt->errno) {
                file_put_contents(__DIR__ . "/notificacion_debug.txt", "ERROR: " . $stmt->error . "\n", FILE_APPEND);
            }
            $stmt->close();

            file_put_contents(__DIR__ . "/notificacion_debug.txt", "NOTIFICACIÓN INSERTADA\n", FILE_APPEND);

            // Comprobar si el cuento quedó vacío
            $stmt = $db->prepare("SELECT current_step FROM stories WHERE id = ?");
            $stmt->bind_param("i", $storyId);
            $stmt->execute();
            $stmt->bind_result($stepCount);
            $stmt->fetch();
            $stmt->close();

            if ($stepCount <= 0) {
                // Eliminar el cuento
                $stmt = $db->prepare("DELETE FROM stories WHERE id = ?");
                $stmt->bind_param("i", $storyId);
                $stmt->execute();
                $stmt->close();

                $db->close();
                header("Location: /StoryTeller/views/moderate_admin.php?story_deleted=1");
                exit;
            }

            $db->close();
            header("Location: /StoryTeller/views/moderate_admin.php?step_deleted=1");
            exit;
        } else {
            $stmt->close();
            $db->close();
            die("Error al eliminar el paso.");
        }
    }

    // Eliminar cuento completo desde botón
    if (isset($_POST['story_id'])) {
        $storyId = intval($_POST['story_id']);

        // Obtener todos los colaboradores y el título del cuento
        $stmt = $db->prepare("SELECT DISTINCT c.user_id, s.title
                              FROM collaborations c
                              JOIN stories s ON c.story_id = s.id
                              WHERE c.story_id = ?");
        $stmt->bind_param("i", $storyId);
        $stmt->execute();
        $result = $stmt->get_result();

        $colaboradores = [];
        $storyTitle = "";
        while ($row = $result->fetch_assoc()) {
            $colaboradores[] = $row['user_id'];
            $storyTitle = $row['title'];
        }
        $stmt->close();

        // Eliminar el cuento
        $stmt = $db->prepare("DELETE FROM stories WHERE id = ?");
        $stmt->bind_param("i", $storyId);

        if ($stmt->execute()) {
            $stmt->close();

            // Notificar a todos los colaboradores
            $mensaje = "El cuento \"" . $storyTitle . "\", en el que participaste, ha sido eliminado por el administrador.";
            foreach ($colaboradores as $userId) {
                $stmt = $db->prepare("INSERT INTO notifications (user_id, type, story_id, message) VALUES (?, 'story_deleted', ?, ?)");
                $stmt->bind_param("iis", $userId, $storyId, $mensaje);
                $stmt->execute();
                $stmt->close();
            }

            $db->close();
            header("Location: /StoryTeller/views/moderate_admin.php?story_deleted=1");
            exit;
        } else {
            $stmt->close();
            $db->close();
            die("Error al eliminar el cuento.");
        }
    }

    $db->close();
    die("Parámetros inválidos.");
} else {
    die("Método no permitido.");
}
