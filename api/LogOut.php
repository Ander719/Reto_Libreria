<?php
// api/Logout.php — Endpoint para cerrar la sesión del usuario
// Requisito profesor: gestión de sesiones (session_unset, session_destroy) con comentarios

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session (gestión de sesiones)
require_once '../Config/Session.php';

// 3. Validación del método HTTP (requisito profesor: códigos HTTP correctos)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use POST para cerrar sesión.',
        'data' => null
    ]);
    exit;
}

// 4. Verificación de que existe una sesión activa antes de intentar cerrarla
// Si no hay sesión, el usuario ya está "desconectado", pero devolvemos 401
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No hay una sesión activa para cerrar.',
        'data' => null
    ]);
    exit;
}

// 5. Destrucción completa de la sesión en 3 pasos:
//    a) Vaciamos el array $_SESSION para eliminar todos los datos almacenados
$_SESSION = [];

//    b) session_unset() libera las variables de sesión registradas
session_unset();

//    c) session_destroy() elimina completamente la sesión del servidor
//       Esto invalida la cookie de sesión y libera los recursos del servidor
session_destroy();

// 6. Respuesta de éxito con código 200
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'code' => 200,
    'message' => 'Sesión cerrada correctamente.',
    'data' => null
]);
?>
