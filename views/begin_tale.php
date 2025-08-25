<?php
/**
 * begin_tale.php – Formulario para iniciar un cuento nuevo
 * -------------------------------------------------------
 * Ruta: /StoryTeller/views/begin_tale.php
 * Dependencias:
 *   - ../includes/db_connect.php   → conexión a la base de datos (PDO)
 *   - ../includes/check_session.php → helpers de sesión (isLoggedIn(), getCurrentUserId(), getCurrentUsername())
 *   - /StoryTeller/js/tale_form_handler.js → lógica de interacción en cliente (validación, ajax, contador de palabras…)
 *   - /StoryTeller/css/styles.css → estilos generales del sitio.
 * 
 * Descripción:
 *   Muestra un formulario para que un usuario cree un nuevo cuento colaborativo, especificando título,
 *   tema, palabra guía, número de colaboraciones y el primer fragmento del texto.
 *   Gestiona la visibilidad de la navegación según el estado de sesión y abre un modal si el usuario
 *   no está autenticado cuando intenta guardar.
 */

include_once '../includes/db_connect.php';
include_once '../includes/check_session.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - StoryTeller project</title>

    <!-- CSS principal (pre‑carga para evitar FOUC) -->
    <link rel="preload" href="/StoryTeller/css/indexcss2.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <!-- Tipografía principal -->
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English&display=swap" rel="stylesheet">
    <!-- Por si falla el preload) -->
    <link rel="stylesheet" href="/../css/indexcss2.css">
</head>

<body>
<header>
        <h1>StoryTeller Project</h1>
        <!-- Navegación principal: se ajusta al estado de sesión -->
        <nav>
            <ul>
                <li><a href="/A01/index.php">Home</a></li>
                <li><a href="/A01/views/stories.html">Stories</a></li>
                <?php if (!isLoggedIn()): ?>
                    <li><a href="/A01/views/login.php">Login</a></li>
                    <li><a href="/A01/views/register.html">Register</a></li>
                <?php else: ?>
                    <li><a href="/A01/views/profile.php">Perfil</a></li>
                    <li><a href="/A01/php/logout.php">Cerrar sesión</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <!-- Mensaje de estado de usuario -->
        <?php if (isLoggedIn()): ?>
            <p class="user-status">Conectado como: <?php echo htmlspecialchars(getCurrentUsername());?></p>
        <?php endif;?>
    </header>
    
    <main>
        <section class="tale-form">
            <h2>Érase una vez...</h2>
            <!-- Formulario para crear cuento -->
            <form id="createStoryForm">
                <?php if (isLoggedIn()): ?>
                    <!-- Se envía el user_id del autor -->
                    <input type="hidden" name="user_id" value="<?php echo getCurrentUserId(); ?>">
                <?php endif; ?>

                <!-- Título del cuento -->
                <div class="form-group">
                    <label for="story-title">Título</label>
                    <input type="text" id="story-title" name="story-title" required>
                </div>

                <!-- Tema / género -->
                <div class="form-group">
                    <label for="story-theme">Tema</label>
                    <select id="story-theme" name="story-theme" required>
                        <option value="">Selecciona un tema</option>
                        <option value="fantasia">Fantasía</option>
                        <option value="terror">Terror</option>
                        <option value="amor">Amor</option>
                        <option value="aventura">Aventura</option>
                        <option value="misterio">Misterio</option>
                        <option value="ciencia-ficcion">Ciencia Ficción</option>
                        <option value="comedia">Comedia</option>
                        <option value="drama">Drama</option>
                        <option value="libre">Tema libre</option>
                    </select>
                </div>

                <!-- Palabra guía -->
                <div class="form-group">
                    <label for="guide-word">Palabra guía</label>
                    <input type="text" id="guide-word" name="guide-word" required>
                </div>

                <!-- Número de colaboraciones (pasos) -->
                <div class="form-group">
                    <label for="story-steps">Nº de colaboraciones</label>
                    <input type="number" id="story-steps" name="story-steps" min="5" max="15" required>
                </div>

                <!-- Primer fragmento del cuento -->
                <div class="form-group story-content">
                    <label for="story-content">Érase una vez...</label>
                    <textarea id="story-content" name="story-content"></textarea>
                    <p id="wordCount">Palabras 0</p>
                </div>

                <!-- Botonera -->
                <div class="button-group">
                    <button type="button" id="btnSaveStory" disabled>Guardar</button>
                    <button type="button" id="btnCancelStory">Cancelar</button>
                </div>
            </form>
        </section>
    </main>

    <!-- JS de interacción del formulario -->
    <script src="/A01/js/tale_form_handler.js"></script>

    <!-- Modal para login/confirmación -->
    <div id="loginModal" class="modal" style="display:none;">
        <div class="modal-content">
            <?php if (!isLoggedIn()): ?>
                <h3>Necesitas una cuenta para guardar tu cuento.</h3>
                <div class="modal-buttons">
                    <button id="btnLogin">Iniciar sesión</button>
                    <button id="btnRegister">Registrarse</button>
                    <button id="btnCancel">Cancelar</button>
                </div>
            <?php else: ?>
                <div>
                    <button id="btnCancel">Cancelar</button>
                    <button type="button" id="btnModalSave">Guardar</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
