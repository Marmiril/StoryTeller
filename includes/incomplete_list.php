<?php
if (!isset($db)) {
    require_once 'db_connect.php';
    $db = connectDB();
}

if (!isset($modo)) {
    $modo = 'index'; 
}

$userId = $_SESSION['user_id'] ?? null;


$conditions = ["current_step < max_steps"];


if ($modo === 'index' && $userId) {
    $conditions[] = "NOT EXISTS (
        SELECT 1 FROM collaborations c
        WHERE c.story_id = s.id AND c.user_id = $userId
    )";
} elseif ($modo === 'profile' && $userId) {
   
    $conditions[] = "(
        s.user_id = $userId OR EXISTS (
            SELECT 1 FROM collaborations c
            WHERE c.story_id = s.id AND c.user_id = $userId
        )
    )";
}


if (!empty($_GET['theme'])) {
    $theme = mysqli_real_escape_string($db, $_GET['theme']);
    $conditions[] = "theme = '$theme'";
}

if (!empty($_GET['steps'])) {
    $steps = (int) $_GET['steps'];
    if ($steps >= 5 && $steps <= 15) {
        $conditions[] = "max_steps = $steps";
    }
}


$currentOrder = $_GET['order'] ?? 'desc';
$nextOrder = ($currentOrder === 'asc') ? 'desc' : 'asc';
$orderSql = $currentOrder === 'asc' ? 'ASC' : 'DESC';
$arrow = $currentOrder === 'asc' ? '🔼' : '🔽';
$queryString = http_build_query(array_merge($_GET, ['order' => $nextOrder]));

$where = implode(' AND ', $conditions);
$query = "SELECT s.id, s.title, s.theme, s.created_at, s.current_step, s.max_steps
          FROM stories s
          WHERE $where
          ORDER BY s.created_at $orderSql";
$result = $db->query($query);
?>

<!-- FILTROS -->
<form method="GET" action="">
    <label for="theme"></label>
    <select id="theme" name="theme">
        <option value="">Todas</option>
        <?php
        $themes_result = $db->query("SELECT DISTINCT theme FROM stories ORDER BY theme ASC");
        while ($theme_row = $themes_result->fetch_assoc()) {
            $selected = ($_GET['theme'] ?? '') === $theme_row['theme'] ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($theme_row['theme']) . '" ' . $selected . '>' . htmlspecialchars($theme_row['theme']) . '</option>';
        }
        ?>
    </select>

    <label for="steps">Nº de pasos:</label>
    <input type="number" id="steps" name="steps" min="5" max="15" value="<?= htmlspecialchars($_GET['steps'] ?? '') ?>">

    <button type="submit">Filtrar</button>

    <a href="?<?= $queryString ?>" style="margin-left: 15px; text-decoration: none; color: inherit; font-weight: bold;">
        Fecha <?= $arrow ?>
    </a>
</form>

<!-- LISTADO -->
<?php
if ($result && $result->num_rows > 0) {
    echo '<ul class="stories">';
    while ($story = $result->fetch_assoc()) {
        echo '<li>';
        echo '<a href="/StoryTeller/php/collaborate_story.php?id=' . $story['id'] . '">' . htmlspecialchars($story['title']) . '</a><br>';
        echo '<small>Tema: ' . htmlspecialchars($story['theme']) . ' | Fecha: ' . date('d/m/Y', strtotime($story['created_at'])) . '</small>';
        echo ' | Paso: ' . ($story['current_step'] + 1) . '/' . $story['max_steps'];
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>No hay cuentos disponibles que coincidan con los filtros.</p>';
}
?>
