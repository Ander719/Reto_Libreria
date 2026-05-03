<?php
// api/Logout.php
header('Content-Type: application/json');

require_once '../controller/ProfileController.php';

$auth = new ProfileController();
$response = $auth->logout();

$isSuccess = !empty($response['success']);
$code = $isSuccess ? 200 : 500;

http_response_code($code);
echo json_encode([
    'status' => $isSuccess ? 'success' : 'error',
    'code' => $code,
    'message' => $isSuccess ? 'Sesión cerrada correctamente' : 'No se pudo cerrar la sesión',
    'data' => null
]);
?>
