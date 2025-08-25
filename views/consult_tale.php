<?php
/**
 * view_story.php – Vista de un cuento en progreso
 * ------------------------------------------------
 * Ruta: /StoryTeller/views/view_story.php
 * 
 * Muestra los datos básicos de un cuento aún no finalizado, junto con la última
 * aportación. Gestiona avisos de estado (ya colaboró / cuento finalizado) y 
 * un mensaje de éxito tras guardar un fragmento.
 * 
 * Dependencias:
 *   - ../includes/header.php        → cabecera común del sitio
 *   - ../includes/footer.php        → pie de página común
 *   - Variables PHP previamente definidas (title, theme, guideWord, etc.)
 *
 * Notas:
 *   • Se asume que el controlador que incluye este archivo prepara las
 *     variables $title, $theme, $guideWord, $currentStep, $maxSteps,
 *     $authorId, $authorName, $lastUserName, $lastDate y $lastWords.
 *   • Si el usuario ya colaboró, se muestra un aviso y un enlace a su perfil.
 *   • Si el cuento está finalizado, se avisa de que no se puede colaborar.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - StoryTeller project</title>
    <!-- Preload de la hoja de estilos principal -->
    <link rel="preload" href="/StoryTeller/css/indexcss2.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <!-- Tipografía IM Fell English -->
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English&display=swap" rel="stylesheet">

    <!-- Hoja de estilos principal -->
    <link rel="stylesheet" href="../css/indexcss2.css">
</head>
<body>
    <!-- Imagen de fondo -->
    <img src="/StoryTeller/images/back_ground_00.png" alt="" id="background-img">

    <div class="wrapper">
        <?php require_once '../includes/header.php'; ?>

        <main class="story-view">
            <!-- Avisos de estado (ya colaboró / cuento finalizado) -->
            <?php if (isset($_GET['notice'])): ?>
                <div class="error-message">
                    <?php if ($_GET['notice'] === 'already_done'): ?>
                        <p>Ya has colaborado en este cuento. Puedes seguir su evolución desde aquí.</p>
                    <?php elseif ($_GET['notice'] === 'finished'): ?>
                        <p>Este cuento ya ha sido finalizado. Ya no se puede colaborar en él.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Mensaje de éxito al guardar un fragmento -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="success-box" id="successMessage" style="display: block;">
                    ¡Tu fragmento ha sido guardado con éxito! 
                    <a href="../php/profile.php" class="btn-profile">Ir a mi perfil</a>
                </div>
            <?php endif; ?>

            <!-- Datos del cuento -->
            <h2><?= htmlspecialchars($title) ?></h2>

            <p><strong>Temática:</strong> <?= htmlspecialchars($theme) ?></p>

            <?php if (!empty($guideWord)): ?>
                <p><strong>Palabra guía:</strong> <?= htmlspecialchars($guideWord) ?></p>
            <?php endif; ?>

            <p><strong>Progreso:</strong> Paso <?= $currentStep ?> de <?= $maxSteps ?></p>

            <?php if ($authorId !== ($_SESSION['user_id'] ?? null)): ?>
                <p><strong>Autor:</strong> <?= htmlspecialchars($authorName) ?></p>
            <?php endif; ?>

            <!-- Última aportación -->
            <section class="last-update">
                <h3>Última aportación...</h3>
                <p><strong>Colaborador:</strong> <?= htmlspecialchars($lastUserName) ?></p>
                <p><strong>Fecha:</strong> <?= date("d/m/Y H:i", strtotime($lastDate)) ?></p>

                <div class="last-fragment">
                    <p><?= nl2br(htmlspecialchars($lastWords)) ?></p>
                </div>
            </section>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>
</body>
</html>
