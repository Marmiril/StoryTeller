<?php
// Incluir la conexión a la base de datos y la comprobación de sesión
require_once '../includes/db_connect.php';
require_once '../includes/check_session.php';

// Si el usuario no está logueado, redirigir a la página de login
if (!isLoggedIn()) {
    header('Location: ../views/login.php');
    exit;
}

// Conectar a la base de datos
$db = connectDB();
$userId = $_SESSION['user_id'];

// Obtener preferencias si existen
$query = "SELECT * FROM user_preferences WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$preferencias = $result->fetch_assoc();
$stmt->close();

// Función auxiliar
function hasUserViewedStory($db, $userId, $storyId): bool {
    $stmt = $db->prepare("SELECT 1 FROM story_views WHERE user_id = ? AND story_id = ?");
    $stmt->bind_param("ii", $userId, $storyId);
    $stmt->execute();
    $stmt->store_result();
    $viewed = $stmt->num_rows > 0;
    $stmt->close();
    return $viewed;
}

// Consulta 1: Cuentos en proceso iniciados por el usuario
$stmt = $db->prepare("SELECT id, title FROM stories WHERE user_id = ? AND finished_at IS NULL");
$stmt->bind_param("i", $userId);
$stmt->execute();
$ownInProgress = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Consulta 2: Cuentos en proceso en los que el usuario ha colaborado (no es el autor)
$stmt = $db->prepare("
    SELECT s.id, s.title
    FROM stories s
    JOIN collaborations c ON c.story_id = s.id
    WHERE c.user_id = ? AND s.user_id != ? AND s.finished_at IS NULL
    GROUP BY s.id
");
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$collabInProgress = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Consulta 3: Cuentos terminados iniciados por el usuario
$stmt = $db->prepare("SELECT id, title FROM stories WHERE user_id = ? AND finished_at IS NOT NULL");
$stmt->bind_param("i", $userId);
$stmt->execute();
$rawResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$ownFinished = [];

foreach ($rawResults as $story) {
    $ownFinished[] = [
        'id' => $story['id'],
        'title' => $story['title'],
        'viewed' => hasUserViewedStory($db, $userId, $story['id'])
    ];
}
$stmt->close();

// Consulta 4: Cuentos terminados en los que el usuario ha colaborado (no es el autor)
$stmt = $db->prepare("
    SELECT s.id, s.title
    FROM stories s
    JOIN collaborations c ON c.story_id = s.id
    WHERE c.user_id = ? AND s.user_id != ? AND s.finished_at IS NOT NULL
    GROUP BY s.id
");
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$rawResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$collabFinished = [];

foreach ($rawResults as $story) {
    $collabFinished[] = [
        'id' => $story['id'],
        'title' => $story['title'],
        'viewed' => hasUserViewedStory($db, $userId, $story['id'])
    ];
}
$stmt->close();

// Consulta 5: Notificaciones del usuario
$stmt = $db->prepare("SELECT id, type, story_id, fragment_id, message, created_at, seen
                      FROM notifications
                      WHERE user_id = ?
                      ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Cerrar conexión a la base de datos
$db->close();

// Incluir la vista que mostrará los datos (HTML)
require_once '../views/profile.php';
