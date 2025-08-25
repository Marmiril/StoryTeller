<?php
require_once '../includes/check_session.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - StoryTeller project</title>
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
                    <li><a href="/StoryTeller/index.php">Inicio</a></li>
                    <li><a href="/StoryTeller/views/register.php">Register</a></li>
                </ul>
            </nav>
        </header>

        <main>

            <h2 class="welcome">Iniciar Sesión</h2>
            <?php
            if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
                echo '<div class="tale-error">';
                foreach ($_SESSION['errors'] as $e) {
                    echo '<p>' . htmlspecialchars($e) . '</p>';
                }
                echo '</div>';
                unset($_SESSION['errors']);
            }
            ?>


            <div class="login-container">
                <form method="POST" action="/StoryTeller/php/login.php">
                    <label for="email">Correo electrónico:</label>
                    <input type="email" id="email" name="email" required
                        value="<?= htmlspecialchars($_SESSION['input_email'] ?? '') ?>">
                    <?php unset($_SESSION['input_email']); ?>

                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>

                    <!-- Redirección -->
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '') ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">

                    <button type="submit" class="btn-tale">Entrar</button>
                </form>
            </div>

            <div class="register-link">
                <p>¿No tienes una cuenta? <a href="/StoryTeller/views/register.php">Regístrate aquí</a></p>
            </div>
        </main>
    </div>
    <?php include '../includes/footer.php'; ?>

</body>

</html>