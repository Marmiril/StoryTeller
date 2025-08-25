<?php
require_once '../includes/check_session.php';
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
if (isset($_GET['step_deleted']) && $_GET['step_deleted'] == 1): ?>
    <div style="background-color: #e7f3ec; color: #155724; padding: 10px; border-left: 5px solid #28a745; margin-bottom: 1rem;">
        Paso eliminado correctamente.
    </div>
<?php endif; ?>

<?php if (isset($_GET['story_deleted']) && $_GET['story_deleted'] == 1): ?>
    <div style="background-color: #fcebea; color: #721c24; padding: 10px; border-left: 5px solid #dc3545; margin-bottom: 1rem;">
        Cuento eliminado correctamente.
    </div>
<?php endif;



if ($_SESSION['user_id'] != 1) {
    echo "<p style='color: red;'>Acceso restringido. Esta sección es solo para el administrador.</p>";
    require_once '../includes/footer.php';
    exit;
}


$query = "SELECT id, title, theme, current_step, max_steps FROM stories ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error al obtener los cuentos: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel de Moderación</title>
    <link rel="stylesheet" href="/StoryTeller/css/admin.css">
</head>

<body>
    <?php require_once '../includes/check_session.php'; ?>
    <?php require_once '../includes/header.php'; ?>

    <h2>Moderación de Cuentos</h2>

    <?php if (mysqli_num_rows($result) == 0): ?>
        <p>No hay cuentos para moderar.</p>
    <?php else: ?>
        <?php while ($story = mysqli_fetch_assoc($result)): ?>
            <div class="story-box" style="border: 1px solid #ccc; padding: 1rem; margin: 1rem 0; border-radius: 8px;">
                <h3><?= htmlspecialchars($story['title']) ?></h3>
                <p><strong>Género:</strong> <?= htmlspecialchars($story['theme']) ?> |
                    <strong>Pasos:</strong> <?= $story['current_step'] ?>/<?= $story['max_steps'] ?>
                </p>


                <form action="../php/admin_delete.php" method="POST" style="margin-top: 0.5rem;">
                    <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                    <button type="submit"
                        onclick="return confirm('¿Estás seguro de que quieres eliminar este cuento completo?') && confirm('Esta acción no se puede deshacer. ¿Eliminar definitivamente este cuento?')"
                        class="btn-delete">
                        Eliminar cuento completo
                    </button>
                </form>


                <a href="javascript:void(0);" class="view-fragments" data-story-id="<?= $story['id'] ?>">Ver Fragmentos</a>


                <div class="fragments-list" id="fragments-<?= $story['id'] ?>" style="display: none;">
                    <?php
                    $storyId = $story['id'];
                    $stmt = $conn->prepare("SELECT c.id, c.content, u.username FROM collaborations c JOIN users u ON c.user_id = u.id WHERE c.story_id = ? ORDER BY c.step_number ASC");
                    $stmt->bind_param("i", $storyId);
                    $stmt->execute();
                    $fragments = $stmt->get_result();
                    while ($fragment = $fragments->fetch_assoc()):
                    ?>
                        <div class="fragment-box" style="border: 1px solid #ddd; padding: 0.5rem; margin: 1rem 0;">
                            <p><strong>Autor:</strong> <?= htmlspecialchars($fragment['username']) ?></p>
                            <p><?= nl2br(htmlspecialchars($fragment['content'])) ?></p>
                            <form action="../php/admin_delete.php" method="POST">
                                <input type="hidden" name="fragment_id" value="<?= $fragment['id'] ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar este paso?')">Eliminar paso</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <script>
        document.querySelectorAll('.view-fragments').forEach(link => {
            link.addEventListener('click', function() {
                const storyId = this.dataset.storyId;
                const fragmentList = document.getElementById('fragments-' + storyId);
                fragmentList.style.display = (fragmentList.style.display === 'none') ? 'block' : 'none';
            });
        });
        setTimeout(() => {
            const alerts = document.querySelectorAll('div[style*="background-color"]');
            alerts.forEach(alert => alert.style.display = 'none');
        }, 3000); 
    </script>

    <?php
    require_once '../includes/footer.php';
    ?>