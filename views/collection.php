<?php
/**
 * story.php – Página de detalle y lectura de un cuento finalizado
 * --------------------------------------------------------------
 * Ruta: /StoryTeller/views/story.php
 * Dependencias:
 *   - ../includes/db_connect.php            → conecta con la BD (función connectDB())
 *   - ../includes/check_session.php         → utilidades de sesión y helpers (isLoggedIn())
 *   - ../php/get_story.php                  → obtiene datos del cuento (func get_story)
 *   - ../php/get_fragments.php              → obtiene los fragmentos del cuento
 *   - ../includes/header.php / footer.php   → cabecera y pie comunes
 *
 * Funcionalidad principal:
 *   • Valida la presencia del parámetro GET ?id.
 *   • Registra la vista del cuento en la tabla story_views (solo una vez por usuario).
 *   • Recupera el cuento y sus fragmentos; aborta si no existe o no está finalizado.
 *   • Muestra los metadatos del cuento en un <aside> y los fragmentos en la sección
 *     principal.
 *   • Ofrece un módulo de estadísticas dinámico mediante peticiones AJAX a
 *     /php/get_statistics.php.
 */

// 1. Validación de parámetro – abortamos pronto si falta «id»
if (!isset($_GET['id'])) {
    echo "Cuento no especificado.";
    exit;
}

require_once '../includes/db_connect.php';
$story_id = intval($_GET['id']);

require_once '../includes/check_session.php';
$user_id = $_SESSION['user_id'] ?? null; // podría ser null si visitante anónimo.

// 2. Registrar la visualización del cuento (solo una vez por usuario).
if ($user_id && $story_id) {
    $db = connectDB();

    // ¿Ya existe un registro de vista?
    $check = $db->prepare("SELECT 1 FROM story_views WHERE user_id = ? AND story_id = ?");
    $check->bind_param("ii", $user_id, $story_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $insert = $db->prepare("INSERT INTO story_views (user_id, story_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $story_id);
        $insert->execute();
        $insert->close();
    }
    $check->close();
}

// 3. Obtener datos del cuento y sus fragmentos.
include '../php/get_story.php';
$story = get_story($story_id);

if (!$story) {
    echo "Cuento no encontrado o no finalizado.";
    exit;
}

include '../php/get_fragments.php'; // $fragments queda disponible.

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($story['title']); ?> - StoryTeller project</title>

    <!-- Fuentes y hoja de estilos común -->
    <link rel="preload" href="/StoryTeller/css/indexcss2.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/indexcss2.css">
</head>

<body>
    <!-- Fondo común -->
    <img src="/StoryTeller/images/back_ground_00.png" alt="" id="background-img">

    <?php include '../includes/header.php'; ?>

    <main class="collection-layout">
        <!-- Información principal del cuento -->
        <aside class="info-box">
            <h3>Datos del cuento</h3>
            <p><strong>Título:</strong> <?php echo htmlspecialchars($story['title']); ?></p>
            <p><strong>Creador:</strong> <?php echo htmlspecialchars($story['creator']); ?></p>
            <p><strong>Temática:</strong> <?php echo htmlspecialchars($story['theme']); ?></p>
            <?php if (!empty($story['guide_word'])): ?>
                <p><strong>Palabra guía:</strong> <?php echo htmlspecialchars($story['guide_word']); ?></p>
            <?php endif; ?>
            <p><strong>Fecha de inicio:</strong> <?php echo htmlspecialchars($story['start_date']); ?></p>
            <p><strong>Fecha de finalización:</strong> <?php echo htmlspecialchars($story['end_date']); ?></p>

            <!-- Listado de colaboradores únicos -->
            <p><strong>Colaboradores:</strong></p>
            <ul>
                <?php
                $authors = [];
                foreach ($fragments as $f) {
                    if (!in_array($f['author'], $authors)) {
                        $authors[] = $f['author'];
                        echo "<li>" . htmlspecialchars($f['author']) . "</li>";
                    }
                }
                ?>
            </ul>

            <!-- Estadísticas dinámicas (solo si cuento finalizado) -->
            <?php if (isset($_GET['id']) && isset($story['end_date'])): ?>
                <section id="statistics">
                    <h3>Estadísticas del cuento</h3>
                    <button id="toggle-stats" style="margin-bottom: 10px;">Ocultar estadísticas</button>
                    <div id="stats-output">Cargando estadísticas...</div>
                </section>

                <script>
                    // Cargar estadísticas vía Fetch API
                    document.addEventListener("DOMContentLoaded", function () {
                        const storyId = <?php echo intval($_GET['id']); ?>;
                        fetch("../php/get_statistics.php?story_id=" + storyId)
                            .then(response => response.json())
                            .then(data => {
                                const statsDiv = document.getElementById("stats-output");
                                if (data.error) {
                                    statsDiv.textContent = data.error;
                                    return;
                                }
                                // Renderizado simple de estadísticas
                                statsDiv.innerHTML = `
                                    <ul>
                                        <li><strong>Edad promedio:</strong> ${parseFloat(data.average_age).toFixed(1)}</li>
                                        <li><strong>Altura promedio:</strong> ${parseFloat(data.average_height).toFixed(2)} cm</li>
                                        <li><strong>Peso promedio:</strong> ${parseFloat(data.average_weight).toFixed(2)} kg</li>
                                        <li><strong>Número favorito promedio:</strong> ${parseFloat(data.average_favorite_number).toFixed(1)}</li>
                                        <li><strong>Color más común:</strong> ${data.most_common_color}</li>
                                        <li><strong>Género:</strong> ${Object.entries(JSON.parse(data.gender_distribution)).map(([g, c]) => `${g}: ${c}`).join(', ')}</li>
                                        <li><strong>Palabras totales:</strong> ${data.total_words}</li>
                                        <li><strong>Fecha de finalización:</strong> ${data.completion_date}</li>
                                    </ul>`;
                            });

                        // Mostrar / ocultar estadísticas
                        const toggleBtn = document.getElementById("toggle-stats");
                        const statsDiv = document.getElementById("stats-output");
                        toggleBtn.addEventListener("click", () => {
                            const hidden = statsDiv.style.display === "none";
                            statsDiv.style.display = hidden ? "block" : "none";
                            toggleBtn.textContent = hidden ? "Ocultar estadísticas" : "Mostrar estadísticas";
                        });
                    });
                </script>
            <?php endif; ?>
        </aside>

        <!-- Contenido completo del cuento (fragmentos ordenados) -->
        <section class="story-content">
            <h2 class="story-title"><?php echo htmlspecialchars($story['title']); ?></h2>
            <?php foreach ($fragments as $fragment): ?>
                <div class="fragment">
                    <h4>Fragmento por <?php echo htmlspecialchars($fragment['author']); ?></h4>
                    <p><?php echo nl2br(htmlspecialchars($fragment['content'])); ?></p>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- Barra lateral con más cuentos -->
        <aside class="collection-list">
            <h3>Más cuentos de la colección</h3>
            <?php include '../includes/collection_list.php'; ?>
        </aside>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
