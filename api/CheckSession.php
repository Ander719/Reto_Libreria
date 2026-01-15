<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

// Verificamos si existe la variable 'user' que creó el AuthController en el Login
if (isset($_SESSION['user'])) {
    
    // CASO 1: Hay sesión activa
    echo json_encode([
        "success" => true,
        "user" => $_SESSION['user'] // Devolvemos el array de datos (id, nombre, rol...)
    ]);

} else {
    
    // CASO 2: No hay sesión (Visitante)
    echo json_encode([
        "success" => false,
        "error" => "No hay sesión activa"
    ]);
}