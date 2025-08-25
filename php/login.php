<?php
require_once '../includes/db_connect.php';

session_start(); // Siempre iniciar sesión al principio
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El formato del correo electrónico no es válido.";
    } else {
        $db = connectDB();
        $stmt = $db->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $errors[] = "No existe ninguna cuenta registrada con ese correo.";
        } else {
            $stmt->bind_result($id, $username, $hashed_password);
            $stmt->fetch();

            if (!password_verify($password, $hashed_password)) {
                $errors[] = "La contraseña es incorrecta.";
            }
        }

        $stmt->close();
        $db->close();
    }

    if (!empty($errors)) {
        $_SESSION['input_email'] = $email;
        $_SESSION['errors'] = $errors; // ✅ Guardamos el array completo
        header("Location: ../views/login.php");
        exit;
    }

    // Autenticación exitosa
    $_SESSION['user_id'] = $id;
    $_SESSION['username'] = $username;

    $redirect = $_POST['redirect'] ?? '';
    $storyId = $_POST['id'] ?? '';

    if ($redirect === 'collaborate_story' && is_numeric($storyId)) {
        header("Location: ../php/collaborate_story.php?id=$storyId");
    } elseif ($redirect === 'create_story') {
        header("Location: ../views/create_story.php");
    } else {
        header("Location: ../php/profile.php");
    }
    exit;
}
