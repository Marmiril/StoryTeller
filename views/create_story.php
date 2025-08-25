<?php
/**
 * create_story.php – Formulario para crear un nuevo cuento (primer fragmento)
 * -------------------------------------------------------------------------
 * Ruta: /StoryTeller/views/create_story.php
 * Dependencias:
 *   - ../includes/db_connect.php      → conexión a la base de datos
 *   - ../includes/check_session.php   → helpers de sesión (isLoggedIn, etc.)
 *   - ../js/tale_form_handler.js      → control de validaciones y recuento de palabras
 * 
 * Descripción:
 * Página donde el usuario introduce el título, temática, número de colaboraciones,
 * palabra guía (opcional) y el primer fragmento (150‑600 palabras).  Si el
 * usuario no ha iniciado sesión se muestra un modal invitándole a registrarse
 * o iniciar sesión antes de guardar.
 */

require_once '../includes/db_connect.php';
require_once '../includes/check_session.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Story – StoryTeller project</title>
    <!-- Preload principal CSS para mejorar rendimiento -->
    <link rel="preload" href="/StoryTeller/css/indexcss2.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English&display=swap" rel="stylesheet">

    <!-- Estilos específicos -->
    <link rel="stylesheet" href="../css/indexcss2.css">
</head>
<body>
    <img src="/StoryTeller/images/back_ground_00.png" alt="" id="background-img">

    <?php require_once '../includes/header.php'; ?>

    <main>
        <section class="welcome">
            <h2>Es hora de comenzar un buen cuento</h2>
        </section>

        <?php
        // ---- Mensajes de error provenientes de create_story.php (backend) -----
        if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
            echo '<div class="tale-error">';
            foreach ($_SESSION['errors'] as $e) {
                echo "<p>" . htmlspecialchars($e) . "</p>";
            }
            echo '</div>';
            unset($_SESSION['errors']);
        }
        ?>

        <!-- Contenedores para mensajes dinámicos -->
        <div id="successMessage" class="message success"></div>
        <div id="errorMessage" class="tale-error"></div>

        <!-- Formulario principal -->
        <section class="tale-form">
            <form id="createStoryForm" action="../php/create_story.php" method="POST">
                <label for="story-title"></label>
                <input type="text" id="story-title" name="title" placeholder="Título" required>

                <label for="story-theme"></label>
                <select id="story-theme" name="theme" required>
                    <option value="" disabled selected>Selecciona una temática</option>
                    <option value="Fantasía">Fantasía</option>
                    <option value="Terror">Terror</option>
                    <option value="Amor">Amor</option>
                    <option value="Aventura">Aventura</option>
                    <option value="Misterio">Misterio</option>
                    <option value="Ciencia Ficción">Ciencia Ficción</option>
                    <option value="Comedia">Comedia</option>
                    <option value="Drama">Drama</option>
                    <option value="Tema libre">Tema libre</option>
                </select>

                <label for="story-steps"></label>
                <input type="number" id="story-steps" name="steps" min="5" max="15" placeholder="Número de colaboraciones (5‑15)" required>

                <label for="guide-word"></label>
                <input type="text" id="guide-word" name="guide_word" placeholder="Palabra guía (opcional)">

                <label for="story-content">Fragmento inicial (150 a 600 palabras):</label>
                <textarea id="story-content" name="fragment" rows="12" required></textarea>
                <p id="wordCount">Nº palabras: 0</p>

                <div class="form-buttons">
                    <button class="btn-tale" type="submit" id="btnSaveStory" disabled>Guardar cuento</button>
                    <button class="btn-tale" type="button" id="btnCancelStory">Cancelar</button>
                </div>
            </form>
        </section>
    </main>

    <!-- JS de validación y modales -->
    <script src="../js/tale_form_handler.js"></script>

    <!-- Modal de login/registro -->
    <div id="loginModal">
        <div class="modal-content">
            <p><b>Para guardar tu cuento necesitas iniciar sesión.</b></p>
            <div class="modal-buttons">
                <button class="btn-tale" id="btnLogin">Iniciar sesión</button>
                <button class="btn-tale" id="btnRegister">Registrarse</button>
                <button class="btn-tale" id="btnCancel">Cancelar</button>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
