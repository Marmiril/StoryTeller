<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$errors = $errors ?? []; 
?>

<!DOCTYPE html>
<html lang="es">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - StoryTeller project</title>
    <link rel="preload" href="/StoryTeller/css/indexcss2.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/indexcss2.css">
</head>

<body>
<img src="/StoryTeller/images/back_ground_00.png" alt="" id="background-img">
    <div class="wrapper">
        <header>
            <h1>StoryTeller Project</h1>
            <nav>
                <ul>
                    <li><a href="../index.php">Inicio</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="register-form">
                <h2>Registro de usuario</h2>

                <form id="userRegistrationForm" action="../php/register.php" method="POST">
                    <!-- Redirección en caso de venir de colaboración -->
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '') ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">

                    <div class="form-group">
                        <label for="username"></label>
                        <input type="text" id="username" name="username" placeholder="Nombre de usuario" required>
                    </div>

                    <div class="form-group">
                        <label for="email"></label>
                        <input type="email" id="email" name="email" placeholder="Correo electrónico" required>
                    </div>

                    <div class="form-group">
                        <label for="password"></label>
                        <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword"></label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirmar contraseña" required>
                    </div>

                    <div class="form-actions">
                        <button class="btn-tale" type="submit">Registrarse</button>
                        <button type="button" id="btnClear" class="btn-tale">Limpiar</button>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/register_validation.js"></script>
</body>

</html>