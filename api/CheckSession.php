<?php
// api/CheckSession.php
session_start();
header("Content-Type: application/json; charset=utf-8");

// 1. CORRECCIÓN: Verificar $_SESSION['user']
if (isset($_SESSION['user'])) {
    
    $user = $_SESSION['user'];
    
    // Si es admin, le añadimos el rol explícitamente para el JS
    if (isset($user['current_account'])) {
        $user['role_type'] = 'admin';
    } else {
        $user['role_type'] = 'user';
    }

    echo json_encode([
        "is_logged" => true,
        "user" => $user
    ]);

} else {
    // No hay sesión
    echo json_encode([
        "is_logged" => false,
        "user" => null
    ]); 
}
?>