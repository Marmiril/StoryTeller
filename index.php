<?php
/**
 * index.php – Página inicial de StoryTeller
 * -------------------------------------------------
 * Muestra la portada de la aplicación con tres columnas:
 *   1. Cuentos inconclusos en los que el usuario puede participar.
 *   2. Sección central con botón para iniciar cuentos y recomendaciones.
 *   3. Colección de cuentos finalizados.
 * 
 * Además gestiona la navegación principal (login/registro/perfil/admin…)
 * y muestra el estado de la sesión.
 *
 * @author Ángel Plata
 * @version 1.0.0
 * @package StoryTeller
 */

require_once 'includes/db_connect.php'; // Conexión a la BBDD.
require_once 'includes/check_session.php'; // Funciones de sesión (isLoggedIn, getCurrentUsername, …).
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
    <link rel="stylesheet" href="../css/indexcss2.css">
</head>

<body>
    <!-- Imagen de fondo fija para extensiones como Dark Reader-->
    <img src="/StoryTeller/images/back_ground_00.png" alt="Fondo acuarelado" id="background-img">

    <div class="wrapper"><!-- Contenedor principal -->
        <header>
            <h1>StoryTeller Project</h1>

            <!-- Navegación superior -->
            <nav>
                <ul>
                    <?php // Enlace visible sólo para el administrador (id = 1).
                    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): ?>
                        <li><a href="/StoryTeller/views/moderate_admin.php">🛠️ Moderación</a></li>
                    <?php endif; ?>

                    <?php // Menú dependiente de la sesión de usuario. ?>
                    <?php if (!isLoggedIn()): ?>
                        <li><a href="/StoryTeller/views/login.php">Login</a></li>
                        <li><a href="/StoryTeller/views/register.php">Register</a></li>
                    <?php else: ?>
                        <li><a href="/StoryTeller/php/profile.php">Perfil</a></li>
                        <li><a href="/StoryTeller/php/logout.php">Cerrar sesión</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <?php // Etiqueta con el nombre del usuario logueado. ?>
            <?php if (isLoggedIn()): ?>
                <p class="user-status">
                    Conectado como: <?= htmlspecialchars(getCurrentUsername()); ?>
                </p>
            <?php endif; ?>
        </header>

        <!-- Mensaje de bienvenida -->
        <section class="welcome">
            <h2>¡Llenemos el mundo de cuentos!</h2>
            <p>Bienvenido a nuestra página de escritores</p>
        </section>

        <!-- Contenido principal con distribución de 3 columnas -->
        <main class="three-columns">
            <div class="columns-container">
                <!-- ▸ Columna izquierda: cuentos en curso -->
                <section class="column">
                    <h2>Los cuentos inconclusos</h2>
                    <div class="stories-list">
                        <?php
                        $modo = 'index'; // Flag para que incomplete_list.php sepa desde dónde lo llaman.
                        include 'includes/incomplete_list.php';
                        ?>
                    </div>
                </section>

                <!-- ▸ Columna central: crear cuento + recomendaciones -->
                <section class="column">
                    <h2>Érase una vez...</h2>
                    <button id="btnCreateStory" class="btn-tale">Crear cuento</button>

                    <section class="recommendations-section">
                        <h3>Antes de empezar</h3>
                        <ul>
                            <!-- Pequeño decálogo para inspirar buenas colaboraciones -->
                            <li>I<br>Empieza el cuento que desees; será interesante ver cómo acaba.</li>
                            <li>II<br>Haz que tu fragmento sea único, siembra ideas que inspiren a los demás.</li>
                            <li>III<br>Respeta el número de palabras y rellena los campos necesarios.</li>
                            <li>IV<br>No resuelvas el final; esto es sólo el comienzo.</li>
                            <li>V<br>Sé amable y respetuoso siempre... todo vuelve.</li>
                            <li>VI<br>Cuida el lenguaje: evita faltas de ortografía; el lenguaje es una joya valiosa.</li>
                        </ul>
                        <button id="btnBeginTale" class="btn-tale">Vamos allá...</button>
                    </section>
                </section>

                <!-- ▸ Columna derecha: cuentos finalizados -->
                <section class="column">
                    <h2>La colección</h2>
                    <div class="completed-stories">
                        <?php include 'includes/collection_list.php'; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- JS que gestiona la interfaz de recomendaciones / modales -->
    <script src="js/recommendations_handler.js"></script>
</body>

</html>
