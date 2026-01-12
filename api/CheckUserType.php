<?php
// api/CheckUserType.php
session_start();
header('Content-Type: application/json');

$response = [
    'isLoggedIn' => false,
    'isAdmin' => false,
    'role' => 'guest'
];

if (isset($_SESSION['profile_code'])) {
    $response['isLoggedIn'] = true;
    $response['role'] = $_SESSION['role'] ?? 'user';
    
    // Verificación estricta
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $response['isAdmin'] = true;
    }
}

echo json_encode($response);
?>