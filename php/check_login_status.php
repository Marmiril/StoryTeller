<?php
session_start();
header('Content-Type: application/json');

$response = [
    "isLoggedIn" => isset($_SESSION['user_id']),
    "user_id" => $_SESSION['user_id'] ?? null
];

echo json_encode($response);
