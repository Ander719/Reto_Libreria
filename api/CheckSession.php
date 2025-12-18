<?php
session_start();
header("Content-Type: application/json");

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Si la sesión existe, devolvemos los datos del usuario guardados en el login
    echo json_encode([
        "is_logged" => true,
        "user" => $_SESSION['user_data']
    ]);
} else {
    // Si no hay sesión válida
    echo json_encode([
        "is_logged" => false
    ]);
}
?>