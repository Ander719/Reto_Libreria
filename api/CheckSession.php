<?php
require_once "../Config/Session.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Content-Type: application/json; charset=utf-8");

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
