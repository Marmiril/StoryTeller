<?php
include_once '../includes/db_connect.php';

$fragments = [];

$query = "SELECT c.content, u.username AS author
          FROM collaborations c
          JOIN users u ON c.user_id = u.id
          WHERE c.story_id = $story_id
          ORDER BY c.step_number ASC";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $fragments[] = [
        'content' => $row['content'],
        'author' => $row['author']
    ];
}
?>
