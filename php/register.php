<?php
require_once '../includes/check_session.php';
require_once '../includes/db_connect.php';

session_start(); // Asegura el uso de $_SESSION

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Correo electrónico inválido.";
    }

    if (empty($errors)) {
        $db = connectDB();
        if (!$db) die("Error de conexión.");

        // Verificar si ya existe el usuario
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "El usuario o email ya existen.";
        } else {
            // Crear usuario
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hash);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;

                // Redirección condicional
                if (isset($_POST['redirect']) && $_POST['redirect'] === 'create_story') {
                    header("Location: ../views/create_story.php");
                } elseif ($_POST['redirect'] === 'collaborate_story' && isset($_POST['story_id'])) {
                    header("Location: ../views/collaborate_story.php?story_id=" . $_POST['story_id']);
                } else {
                    header("Location: ../php/profile.php");
                }
                exit;
            } else {
                $errors[] = "Error al registrar el usuario.";
            }
        }

        $stmt->close();
        $db->close();
    }
}
