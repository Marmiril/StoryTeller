<?php
require_once __DIR__ . '/../config/config.php';

function connectDB() {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conexion->set_charset("utf8mb4");

    if ($conexion->connect_error) {
        if (ENVIRONMENT === 'development') {
            // Desarrollo
            die("Error de conexión: " . $conexion->connect_error);
        } else {
            // Error y mostrar mensaje genérico
            error_log("DB connection error: " . $conexion->connect_error);
            die("Error al conectar con la base de datos. Inténtalo más tarde.");
        }
    }

    return $conexion;
}
?>
