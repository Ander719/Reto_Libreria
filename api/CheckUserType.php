<?php
// api/CheckUserType.php
session_start();
header('Content-Type: application/json');

$response = [
    'isLoggedIn' => false,
    'isAdmin' => false,
    'role' => 'guest'
];

// 1. CORRECCIÓN: Buscamos $_SESSION['user'] (la nueva estructura)
if (isset($_SESSION['user'])) {
    $response['isLoggedIn'] = true;
    
    // En la nueva estructura, 'current_account' solo existe si es Admin
    if (isset($_SESSION['user']['current_account']) && !empty($_SESSION['user']['current_account'])) {
        $response['isAdmin'] = true;
        $response['role'] = 'admin';
    } else {
        $response['role'] = 'user';
    }
}

echo json_encode($response);
?>