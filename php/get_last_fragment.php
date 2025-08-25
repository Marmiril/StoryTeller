<?php
$last_fragment = "";

$query = "SELECT content FROM fragments WHERE story_id = $story_id ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    $last_fragment = $row['content'];
}
?>
