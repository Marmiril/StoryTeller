<?php
if (!isset($db)) {
    require_once '../includes/db_connect.php';
    $db = connectDB();
}

// Orden
$currentOrder = $_GET['collection_order'] ?? 'desc';
$nextOrder = ($currentOrder === 'asc') ? 'desc' : 'asc';
$arrow = $currentOrder === 'asc' ? '🔼' : '🔽';
$queryString = http_build_query(array_merge($_GET, ['collection_order' => $nextOrder]));
?>

<!-- FILTROS -->
<form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
    <?php if (isset($_GET['id'])): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id']) ?>">
    <?php endif; ?>

    <label for="collection_theme"></label>
    <select id="collection_theme" name="collection_theme">
        <option value="">Todas</option>
        <?php
        $themes_result = $db->query("SELECT DISTINCT theme FROM stories ORDER BY theme ASC");
        while ($theme_row = $themes_result->fetch_assoc()) {
            $selected = ($_GET['collection_theme'] ?? '') === $theme_row['theme'] ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($theme_row['theme']) . '" ' . $selected . '>' . htmlspecialchars($theme_row['theme']) . '</option>';
        }
        ?>
    </select>

    <label for="collection_steps">Nº de pasos:</label>
    <input type="number" id="collection_steps" name="collection_steps" min="5" max="15" value="<?= htmlspecialchars($_GET['collection_steps'] ?? '') ?>">

    <button type="submit">Filtrar</button>
    <a href="?<?= $queryString ?>" style="font-weight: normal; text-decoration: none; color: inherit;">
        Fecha <?= $arrow ?>
    </a>
</form>

<!-- LISTADO -->
<?php
$conditions = ["current_step >= max_steps"];

if (!empty($_GET['collection_theme'])) {
    $theme = mysqli_real_escape_string($db, $_GET['collection_theme']);
    $conditions[] = "theme = '$theme'";
}

if (!empty($_GET['collection_steps'])) {
    $steps = (int) $_GET['collection_steps'];
    if ($steps >= 5 && $steps <= 15) {
        $conditions[] = "max_steps = $steps";
    }
}

$where = implode(" AND ", $conditions);
$order = $currentOrder === 'asc' ? 'ASC' : 'DESC';

$query = "SELECT id, title, theme, created_at FROM stories WHERE $where ORDER BY finished_at $order";
$result = $db->query($query);

if ($result && $result->num_rows > 0) {
    echo '<ul class="stories">';
    while ($story = $result->fetch_assoc()) {
        $queryString = http_build_query(array_merge($_GET, ['id' => $story['id']]));
        $active = (isset($_GET['id']) && $_GET['id'] == $story['id']) ? ' style="font-weight: bold;"' : '';

        echo '<li><a href="/StoryTeller/views/collection.php?' . $queryString . '"' . $active . '>';
        echo htmlspecialchars($story['title']) . '</a><br>';
        echo '<small>Tema: ' . htmlspecialchars($story['theme']) . ' | ';
        echo 'Fecha: ' . date('d/m/Y', strtotime($story['created_at'])) . '</small>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>No hay cuentos completados que coincidan con los filtros.</p>';
}
?>
