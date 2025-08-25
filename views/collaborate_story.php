<?php
/**
 * collaborate.php – Pantalla de colaboración sobre un cuento existente
 * ---------------------------------------------------------------
 * Ruta: /StoryTeller/views/collaborate.php
 * 
 * Este archivo muestra la última aportación del cuento y un formulario
 * para que el usuario añada un nuevo fragmento (si cumple los requisitos
 * de sesión y límites de pasos).  Gestiona también la apertura del modal
 * de login/registro cuando el visitante no está autenticado.
 * 
 * Dependencias:
 *   - ../includes/header.php  → cabecera y navegación (maneja sesión)
 *   - ../includes/footer.php  → pie de página
 *   - /StoryTeller/js/collaborate_handler.js       → lógica de conteo de palabras y validación
 *   - /StoryTeller/js/collaborate_modal_handler.js → control de modal de login/registro
 *   - ../php/save_collaboration.php               → endpoint para guardar colaboración
 * 
 * Variables recibidas desde el controlador:
 *   $title, $theme, $guideWord          → metadatos del cuento
 *   $currentStep, $maxSteps             → progreso y límite de pasos
 *   $lastUserName, $lastDate, $lastWords→ datos de la última aportación
 *   $storyId                            → id del cuento
 *   $canCollaborate (bool)              → permiso para colaborar (según sesión y pasos)
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colaborar – StoryTeller project</title>

    <!-- Precarga de CSS principal -->
    <link rel="preload" href="/StoryTeller/css/indexcss2.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English&display=swap" rel="stylesheet">

    <!-- Hoja de estilos local (versión scss compilada para colaboración) -->
    <link rel="stylesheet" href="../css/indexcss2.css">
</head>

<body>
    <!-- Imagen de fondo general de la app -->
    <img src="/StoryTeller/images/back_ground_00.png" alt="" id="background-img">

    <?php require_once '../includes/header.php'; ?>

    <div class="wrapper">
        <main class="collab-form-container">
            <!-- Mensaje de éxito tras guardar colaboraciones vía JS -->
            <div id="successMessage" style="display: none;"></div>

            <!-- Encabezado con datos del cuento -->
            <h2><?= htmlspecialchars($title) ?></h2>
            <p><strong>Temática:</strong> <?= htmlspecialchars($theme) ?></p>

            <?php if (!empty($guideWord)): ?>
                <p><strong>Palabra guía:</strong> <?= htmlspecialchars($guideWord) ?></p>
            <?php endif; ?>

            <p><strong>Progreso:</strong> Paso <?= $currentStep ?> de <?= $maxSteps ?></p>

            <!-- Último fragmento mostrado como referencia -->
            <section class="last-fragment-section">
                <h3>Última aportación</h3>
                <p><strong>Colaborador:</strong> <?= htmlspecialchars($lastUserName) ?></p>
                <p><strong>Fecha:</strong> <?= date("d/m/Y H:i", strtotime($lastDate)) ?></p>
                <div class="last-fragment-box">
                    <p><?= nl2br(htmlspecialchars($lastWords)) ?></p>
                </div>
            </section>

            <?php if (!empty($canCollaborate)): ?>
                <!-- Formulario para añadir nuevo fragmento -->
                <form id="collabForm" action="../php/save_collaboration.php" method="POST">
                    <input type="hidden" name="story_id" value="<?= $storyId ?>">

                    <label for="collab-content">Tu fragmento (150 a 600 palabras):</label>
                    <textarea id="collab-content"
                              name="content"
                              rows="10"
                              required
                              data-story-id="<?= $storyId ?>"></textarea>

                    <p id="collabWordCount">Nº palabras: 0</p>

                    <div class="form-actions">
                        <button class="btn-tale" type="submit" id="btnSaveCollab">Guardar colaboración</button>
                        <button class="btn-tale" type="button" id="btnCancelCollab">Cancelar</button>
                    </div>
                </form>
                <!-- Script para validar y contabilizar palabras -->
                <script src="/StoryTeller/js/collaborate_handler.js"></script>
            <?php else: ?>
                <!-- Bloque mostrado a invitados o cuando se alcanzó el máximo de pasos -->
                <div class="form-actions">
                    <button class="btn-tale" id="btnStartCollab">Colaborar ahora</button>
                </div>

                <!-- Modal de autenticación -->
                <div id="loginModal">
                    <div class="modal-content">
                        <p><b>Para colaborar necesitas iniciar sesión o registrarte.</b></p>
                        <div class="modal-buttons">
                            <button class="btn-tale" id="btnLogin">Iniciar sesión</button>
                            <button class="btn-tale" id="btnRegister">Registrarse</button>
                            <button class="btn-tale" id="btnCancel">Cancelar</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Control de modal para login/registro y cancelaciones -->
    <script src="/StoryTeller/js/collaborate_modal_handler.js"></script>
</body>

</html>
