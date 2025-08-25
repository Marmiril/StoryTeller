<?php
require_once '../includes/db_connect.php';
require_once '../includes/check_session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$storyId = $_POST['story_id'] ?? null;
$content = trim($_POST['content'] ?? '');

if (!$userId || !$storyId || !is_numeric($storyId) || empty($content)) {
    die("Datos incompletos o inválidos.");
}

// Contar palabras
$wordCount = str_word_count(strip_tags($content));
if ($wordCount < 150 || $wordCount > 600) {
    die("El fragmento debe tener entre 150 y 600 palabras.");
}

$db = connectDB();

// Verificar si ya colaboró
$stmt = $db->prepare("SELECT COUNT(*) FROM collaborations WHERE story_id = ? AND user_id = ?");
$stmt->bind_param("ii", $storyId, $userId);
$stmt->execute();
$stmt->bind_result($exists);
$stmt->fetch();
$stmt->close();

if ($exists > 0) {
    die("Ya has colaborado en esta historia.");
}

// Obtener paso actual y máximo
$stmt = $db->prepare("SELECT current_step, max_steps, title FROM stories WHERE id = ?");
$stmt->bind_param("i", $storyId);
$stmt->execute();
$stmt->bind_result($currentStep, $maxSteps, $title);
$stmt->fetch();
$stmt->close();

// Insertar nueva colaboración
$stepNumber = $currentStep + 1;
$stmt = $db->prepare("INSERT INTO collaborations (story_id, user_id, content, step_number)
                      VALUES (?, ?, ?, ?)");
$stmt->bind_param("iisi", $storyId, $userId, $content, $stepNumber);
$stmt->execute();
$stmt->close();

// Si se completa el cuento, concatenar todos los fragmentos y finalizarlo
if ($stepNumber >= $maxSteps) {
    $stmt = $db->prepare("SELECT content FROM collaborations WHERE story_id = ? ORDER BY step_number ASC");
    $stmt->bind_param("i", $storyId);
    $stmt->execute();
    $result = $stmt->get_result();

    $fullText = '';
    while ($row = $result->fetch_assoc()) {
        $fullText .= $row['content'] . "\n\n";
    }
    $stmt->close();

    $stmt = $db->prepare("UPDATE stories SET current_step = ?, full_text = ?, finished_at = NOW() WHERE id = ?");
    $stmt->bind_param("isi", $stepNumber, $fullText, $storyId);
    $stmt->execute();
    $stmt->close();

    // Generar estadísticas
    define('IN_SAVE_COLLABORATION', true);
    $_GET['id'] = $storyId;
    include '../php/generate_statistics.php';

    // Enviar notificación
    require_once '../includes/send_completion_notification.php';
    sendCompletionNotification($storyId, $title);
} else {
    // Solo actualizar el paso
    $stmt = $db->prepare("UPDATE stories SET current_step = ? WHERE id = ?");
    $stmt->bind_param("ii", $stepNumber, $storyId);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../php/consult_tale.php?id=$storyId&success=1");
exit;
