<?php
require_once __DIR__ . '/includes/check_session.php';
require_once __DIR__ . '/includes/db_connect.php';
session_start();

$errors = [];
$success = false;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Historia no válida.");
}

$story_id = (int) $_GET['id'];
$db = connectDB();
if (!$db) die("Error de conexión.");

// Obtener historia
$stmt = $db->prepare("SELECT s.title, s.theme, s.guide_word, s.max_steps, s.current_step, s.user_id FROM stories s WHERE s.id = ?");
$stmt->bind_param("i", $story_id);
$stmt->execute();
$stmt->bind_result($title, $theme, $guide_word, $max_steps, $current_step, $story_owner);
$stmt->fetch();
$stmt->close();

if ($current_step >= $max_steps) {
    die("Esta historia ya está completa.");
}

// Verificar si el usuario ya ha colaborado
$stmt = $db->prepare("SELECT id FROM collaborations WHERE story_id = ? AND user_id = ?");
$stmt->bind_param("ii", $story_id, $_SESSION['user_id']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die("Ya has colaborado en esta historia.");
}
$stmt->close();

// Procesar envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fragment = trim($_POST['fragment']);

    if (str_word_count($fragment) < 150 || str_word_count($fragment) > 600) {
        $errors[] = "El fragmento debe tener entre 150 y 600 palabras.";
    }

    if (empty($errors)) {
        $next_step = $current_step + 1;

        // Insertar colaboración
        $stmt = $db->prepare("INSERT INTO collaborations (story_id, user_id, content, step_number) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $story_id, $_SESSION['user_id'], $fragment, $next_step);
        $stmt->execute();
        $stmt->close();

        // Si se alcanza el último paso...
        if ($next_step >= $max_steps) {
            // Reunir todos los fragmentos
            $full_text = '';
            $stmt = $db->prepare("SELECT content FROM collaborations WHERE story_id = ? ORDER BY step_number ASC");
            $stmt->bind_param("i", $story_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $full_text .= $row['content'] . "\n\n"; // ← CORRECTO
            }
            $stmt->close();

        // Guardar texto completo y cerrar la historia
        $stmt = $db->prepare("UPDATE stories SET current_step = ?, finished_at = NOW(), full_text = ? WHERE id = ?");
        $stmt->bind_param("isi", $next_step, $full_text, $story_id);
        $stmt->execute();
        $stmt->close();

        // Ejecutar estadísticas automáticamente
        require_once __DIR__ . '/generate_statistics.php';

        } else {
            // Actualizar paso solamente
            $stmt = $db->prepare("UPDATE stories SET current_step = ? WHERE id = ?");
            $stmt->bind_param("ii", $next_step, $story_id);
            $stmt->execute();
            $stmt->close();
        }

        $success = true;
    }
}

$db->close();
?>
<?php
// Mostrar últimas 50 palabras de la colaboración más reciente
$stmt = $db->prepare("SELECT content FROM collaborations WHERE story_id = ? ORDER BY step_number DESC LIMIT 1");
$stmt->bind_param("i", $story_id);
$stmt->execute();
$result = $stmt->get_result();
$last_fragment = "";

if ($row = $result->fetch_assoc()) {
    $words = explode(" ", strip_tags($row['content']));
    $last_words = array_slice($words, -50);
    $last_fragment = implode(" ", $last_words);
}
$stmt->close();
?>

<?php if (!empty($last_fragment)): ?>
    <div style="border:1px solid #ccc; padding:10px; margin:20px 0; background:#f9f9f9;">
        <strong>Últimas 50 palabras del cuento:</strong>
        <p><?= htmlspecialchars($last_fragment) ?></p>
    </div>
<?php endif; ?>


<h2>Colaborar en la historia: <?= htmlspecialchars($title) ?></h2>
<p><strong>Temática:</strong> <?= htmlspecialchars($theme) ?></p>
<?php if (!empty($guide_word)): ?>
    <p><strong>Palabra guía:</strong> <?= htmlspecialchars($guide_word) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;">¡Tu colaboración se ha registrado con éxito!</p>
<?php else: ?>
    <?php foreach ($errors as $e) echo "<p style='color:red;'>$e</p>"; ?>
    <form method="POST">
        <textarea name="fragment" placeholder="Escribe aquí tu fragmento (150–600 palabras)" rows="10" required></textarea><br>
        <button type="submit">Enviar colaboración</button>
    </form>
<?php endif; ?>
