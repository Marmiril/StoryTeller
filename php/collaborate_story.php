<?php
require_once '../includes/db_connect.php';
require_once '../includes/check_session.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de historia no válido.");
}

$storyId = (int)$_GET['id'];
$db = connectDB();

$userId = $_SESSION['user_id'] ?? null;

$canCollaborate = false; // Por defecto

if ($userId) {
    // Verificar si ya ha colaborado
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM collaborations
        WHERE story_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $storyId, $userId);
    $stmt->execute();
    $stmt->bind_result($collabCount);
    $stmt->fetch();
    $stmt->close();

    if ($collabCount > 0) {
        // Redirigir si ya colaboró
        header("Location: ../php/consult_tale.php?id=$storyId&notice=already_done");
        exit;
    } else {
        $canCollaborate = true; // Está logueado y no ha colaborado
    }
}

// Obtener datos generales del cuento
$stmt = $db->prepare("
    SELECT s.title, s.theme, s.guide_word, s.max_steps, s.current_step, s.user_id, u.username, s.finished_at
    FROM stories s
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
");
$stmt->bind_param("i", $storyId);
$stmt->execute();
$stmt->bind_result($title, $theme, $guideWord, $maxSteps, $currentStep, $authorId, $authorName, $finishedAt);
$stmt->fetch();
$stmt->close();

// ✅ Validar si ya está finalizado
if ($finishedAt !== null) {
    header("Location: ../php/consult_tale.php?id={$storyId}&notice=finished");
    exit;
}

// Obtener último fragmento
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

// Mostrar mensaje si esta es la última colaboración
if ($currentStep == $maxSteps - 1) {
    echo '
    <div style="
        max-width: 500px;
        margin: 40px auto;
        background-color: #fff3cd;
        color: #856404;
        border: 2px solid #ffeeba;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        font-size: 1.1em;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    ">
        <strong>Última colaboración</strong><br>Tu fragmento cerrará el cuento.
    </div>';
}

// Obtener nombre del último colaborador
$stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $lastUserId);
$stmt->execute();
$stmt->bind_result($lastUserName);
$stmt->fetch();
$stmt->close();

// Extraer últimas 50 palabras del fragmento
$words = preg_split('/\\s+/', strip_tags($lastContent));
$lastWords = implode(" ", array_slice($words, -50));

$db->close();




// Cargar vista con botón "Colaborar ahora"
require_once '../views/collaborate_story.php';
