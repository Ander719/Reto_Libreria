<?php
// api/Logout.php
header('Content-Type: application/json; charset=utf-8');
require_once '../Config/Session.php';

session_unset();
session_destroy();

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'code' => 200,
    'message' => 'Sesión cerrada correctamente',
    'data' => null
]);
?>
