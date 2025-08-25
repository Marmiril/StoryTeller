<?php
require_once '../includes/db_connect.php';
require_once '../includes/check_session.php';

// Validar ID del cuento
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de historia no válido.");
}

$storyId = (int)$_GET['id'];
$db = connectDB();

// Obtener datos generales del cuento y su autor
$stmt = $db->prepare("
    SELECT s.title, s.theme, s.guide_word, s.max_steps, s.current_step, s.user_id, u.username
    FROM stories s
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
");
$stmt->bind_param("i", $storyId);
$stmt->execute();
$stmt->bind_result($title, $theme, $guideWord, $maxSteps, $currentStep, $authorId, $authorName);
$stmt->fetch();
$stmt->close();

// Comprobar si el usuario tiene acceso
$hasCollaborated = false;
$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM collaborations
        WHERE story_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $storyId, $userId);
    $stmt->execute();
    $stmt->bind_result($collabCount);
    $stmt->fetch();
    $hasCollaborated = $collabCount > 0;
    $stmt->close();
}

// Obtener el último fragmento
$stmt = $db->prepare("
    SELECT content, created_at, user_id
    FROM collaborations
    WHERE story_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->bind_param("i", $storyId);
$stmt->execute();
$stmt->bind_result($lastContent, $lastDate, $lastUserId);
$stmt->fetch();
$stmt->close();

// Obtener nombre del último colaborador
$stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $lastUserId);
$stmt->execute();
$stmt->bind_result($lastUserName);
$stmt->fetch();
$stmt->close();

// Extraer últimas 50 palabras del contenido
$words = preg_split('/\\s+/', strip_tags($lastContent));
$lastWords = implode(" ", array_slice($words, -50));

// Cerrar conexión
$db->close();

// Cargar vista
require_once '../views/consult_tale.php';
?>
