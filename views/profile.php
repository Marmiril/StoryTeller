<?php
require_once '../includes/db_connect.php';
require_once '../includes/check_session.php';

// Obtener preferencias si existen
$userId = $_SESSION['user_id'];
$query = "SELECT * FROM user_preferences WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$preferencias = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - StoryTeller project</title>
    <link rel="preload" href="/StoryTeller/css/indexcss2.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/indexcss2.css">
</head>

<body>
    <img src="/StoryTeller/images/back_ground_00.png" alt="" id="background-img">
    <div class="wrapper">
        <header>
            <h1>StoryTeller Project</h1>
            <nav>
                <ul>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): ?>
                        <li><a href="/StoryTeller/views/moderate_admin.php">🛠️ Moderación</a></li>
                    <?php endif; ?>
                    <li><a href="/StoryTeller/index.php">Inicio</a></li>
                    <li><a href="/StoryTeller/php/logout.php">Cerrar sesión</a></li>
                </ul>
            </nav>


        </header>
        <section class="welcome">
            <p>
                Conectado como: <?= htmlspecialchars(getCurrentUsername()); ?>
            </p>
        </section>

        <main class="three-columns">
            <div class="columns-container">
                <!-- Columna izquierda -->
                <section class="column">
                    <h2>Cuentos en proceso</h2>
                    <div class="stories-list">
                        <h3>Iniciados por mí</h3>
                        <ul class="tale-list">
                            <?php foreach ($ownInProgress as $story): ?>
                                <li>
                                    <a href="/StoryTeller/php/consult_tale.php?id=<?= $story['id'] ?>">
                                        <?= htmlspecialchars($story['title']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <h3>Colaborados por mí</h3>
                        <ul class="tale-list">
                            <?php foreach ($collabInProgress as $story): ?>
                                <li>
                                    <a href="/StoryTeller/php/consult_tale.php?id=<?= $story['id'] ?>">
                                        <?= htmlspecialchars($story['title']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>


                <!-- Columna central -->
                <section class="column">
                    <h2>Érase una vez...</h2>
                    <button id="btnCreateStory" class="btn-tale">Crear cuento</button>

                    <section class="recommendations-section">
                        <h3>Antes de empezar</h3>
                        <ul>
                            <li>I<br>Empieza el cuento que desees, será interesante ver como acaba.</li>
                            <li>II<br>Haz que tu fragmento sea único, siembra ideas que inspiren a los demás.</li>
                            <li>III<br>Respeta el número de palabras y rellena los campos necesarios para poder guardar tu colaboración.</li>
                            <li>IV<br>No resuelvas el final del cuento, esto es sólo el comienzo.</li>
                            <li>V<br>Sé amable y respetuoso siempre... todo vuelve.</li>
                            <li>VI<br>Cuida el lenguaje, evita las faltas de ortografía, el lenguaje es una joya valiosa.</li>
                        </ul>
                        <button id="btnBeginTale" class="btn-tale">Vamos allá...</button>
                    </section>
                </section>
                <?php if (isset($notifications) && count($notifications) > 0): ?>
                    <section class="column" class="notifications" style="margin-top: 2rem;">
                        <h2>🔔 Notificaciones recientes</h2>
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach ($notifications as $notif): ?>
                                <li style="margin-bottom: 1rem; background: #f9f9f9; padding: 1rem; border: 1px solid #ddd; border-radius: 6px;">
                                    <p style="margin: 0 0 0.5rem;">
                                        <?= htmlspecialchars($notif['message']) ?>
                                    </p>
                                    <small style="color: #666;">
                                        <?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?>
                                    </small>
                                </li>
                                <form action="/StoryTeller/php/delete_notification.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                                    <button type="submit" style="background: none; color: red; border: none; cursor: pointer; font-size: 0.9rem;">
                                        X Eliminar
                                    </button>
                                </form>

                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <!-- Columna derecha -->
                <section class="column">
                    <h2>Mi colección</h2>
                    <div class="completed-stories">

                        <h3>Iniciados por mí</h3>
                        <ul class="tale-list">
                            <?php foreach ($ownFinished as $story): ?>
                                <?php
                                $style = (!$story['viewed'])
                                    ? 'color: green; font-weight: bold;'
                                    : '';
                                ?>
                                <li>
                                    <a href="/StoryTeller/php/show_collection.php?id=<?= $story['id'] ?>" style="<?= $style ?>">
                                        <?= htmlspecialchars($story['title']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <h3>Colaborados por mí</h3>
                        <ul class="tale-list">
                            <?php foreach ($collabFinished as $story): ?>
                                <?php
                                $style = (!$story['viewed'])
                                    ? 'color: green; font-weight: bold;'
                                    : '';
                                ?>
                                <li>
                                    <a href="/StoryTeller/php/show_collection.php?id=<?= $story['id'] ?>" style="<?= $style ?>">
                                        <?= htmlspecialchars($story['title']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            </div>

            <aside class="preferences-sidebar">
                <h2>Mis preferencias</h2>

                <div id="preferences-view" class="preferencesForm" style="<?= $preferencias ? '' : 'display:none;' ?>">
                    <label for="favorite_color">Color favorito:</label>
                    <input type="text" name="favorite_color" id="favorite_color"
                        value="<?= htmlspecialchars($preferencias['favorite_color'] ?? '') ?>" readonly>

                    <label for="favorite_number">Número favorito:</label>
                    <input type="number" name="favorite_number" id="favorite_number"
                        value="<?= htmlspecialchars($preferencias['favorite_number'] ?? '') ?>" readonly>

                    <label for="height">Altura (cm):</label>
                    <input type="number" name="height" id="height" step="0.1"
                        value="<?= htmlspecialchars($preferencias['height'] ?? '') ?>" readonly>

                    <label for="weight">Peso (kg):</label>
                    <input type="number" name="weight" id="weight" step="0.1"
                        value="<?= htmlspecialchars($preferencias['weight'] ?? '') ?>" readonly>

                    <label for="age">Edad:</label>
                    <input type="number" name="age" id="age"
                        value="<?= htmlspecialchars($preferencias['age'] ?? '') ?>" readonly>

                    <label for="gender">Género:
                        <?= htmlspecialchars($preferencias['gender'] ?? 'No definido') ?>
                    </label>

                    <button class="btn-tale" id="edit-preferences-btn">Añade o modifica tus preferencias</button>
                </div>

                <!-- FORMULARIO EDITABLE -->
                <form id="preferencesForm" class="preferencesForm" action="/StoryTeller/php/save_preferences.php" method="POST"
                    style="<?= $preferencias ? 'display:none;' : '' ?>">
                    <input type="hidden" name="user_id" value="<?= $userId ?>">

                    <label for="favorite_color">Color favorito:</label>
                    <input type="text" name="favorite_color" id="favorite_color"
                        value="<?= htmlspecialchars($preferencias['favorite_color'] ?? '') ?>">

                    <label for="favorite_number">Número favorito:</label>
                    <input type="number" name="favorite_number" id="favorite_number"
                        value="<?= htmlspecialchars($preferencias['favorite_number'] ?? '') ?>">

                    <label for="height">Altura (cm):</label>
                    <input type="number" name="height" id="height" step="0.1"
                        value="<?= htmlspecialchars($preferencias['height'] ?? '') ?>">

                    <label for="weight">Peso (kg):</label>
                    <input type="number" name="weight" id="weight" step="0.1"
                        value="<?= htmlspecialchars($preferencias['weight'] ?? '') ?>">

                    <label for="age">Edad:</label>
                    <input type="number" name="age" id="age"
                        value="<?= htmlspecialchars($preferencias['age'] ?? '') ?>">

                    <label>Género:</label>
                    <div id="gender">
                        <label><input type="radio" name="gender" value="M"
                                <?= ($preferencias['gender'] ?? '') === 'M' ? 'checked' : '' ?>> Masculino</label>
                        <label><input type="radio" name="gender" value="F"
                                <?= ($preferencias['gender'] ?? '') === 'F' ? 'checked' : '' ?>> Femenino</label>
                    </div>
                    <div class="form-buttons">
                    <button class="btn-tale" type="submit">Guardar</button>
                    <button class="btn-tale" type="button" id="cancel-edit-btn">Cancelar</button>
                    </div>
                </form>
            </aside>
        </main>


        <script src="/StoryTeller/js/preferences_validation.js"></script>
        <script src="/StoryTeller/js/recommendations_handler.js"></script>

    </div>
    <?php include '../includes/footer.php'; ?>
</body>

</html>