<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../Config/Session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido.',
        'data' => null
    ]);
    exit;
}

if (isset($_SESSION['user'])) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Sesión activa',
        'data' => [
            'user' => $_SESSION['user']
        ]
    ]);

} else {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No hay sesión activa',
        'data' => null
    ]);
}
