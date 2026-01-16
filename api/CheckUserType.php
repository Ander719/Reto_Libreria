<?php
// api/CheckUserType.php
session_start();
header('Content-Type: application/json');

$response = [
    'isLoggedIn' => false,
    'isAdmin' => false,
    'role' => 'guest'
];

// CORRECCIÓN: Buscamos en 'user_data', que es donde Login.php guarda los datos
if (isset($_SESSION['user_data'])) {
    $response['isLoggedIn'] = true;
    
    // Obtenemos el rol (asegurando que existe)
    $role = $_SESSION['user_data']['rol'] ?? 'user';
    $response['role'] = $role;
    
    // Verificamos si es admin
    if ($role === 'admin') {
        $response['isAdmin'] = true;
    }
}

echo json_encode($response);
?>