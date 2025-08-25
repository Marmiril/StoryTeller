<?php
include_once '../includes/db_connect.php';

function get_story($id) {
    global $conn;

    $sql = "SELECT s.title, s.theme, s.guide_word, s.created_at AS start_date, s.finished_at AS end_date,
                   u.username AS creator
            FROM stories s
            JOIN users u ON s.user_id = u.id
            WHERE s.id = $id";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    } else {
        return null;
    }
}
?>
