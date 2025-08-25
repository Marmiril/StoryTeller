<?php
require_once __DIR__ . '/../includes/check_session.php';
?>

<header>
    <h1>StoryTeller Project</h1>
    <nav>
        <ul>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): ?>
                <li><a href="/StoryTeller/views/moderate_admin.php">🛠️ Moderación</a></li>
            <?php endif; ?>
            <li><a href="/StoryTeller/index.php">Inicio</a></li>
            <?php if (!isLoggedIn()): ?>
                <li><a href="/StoryTeller/views/login.php">Login</a></li>
                <li><a href="/StoryTeller/views/register.php">Register</a></li>
            <?php else: ?>
                <li><a href="/StoryTeller/php/profile.php">Perfil</a></li>
                <li><a href="/StoryTeller/php/logout.php">Cerrar sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <?php if (isLoggedIn()): ?>
        <p class="user-status">
            Conectado como: <?= htmlspecialchars(getCurrentUsername()); ?>
        </p>
    <?php endif; ?>
</header>