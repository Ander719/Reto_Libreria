<?php
// api/CheckSession.php — Endpoint para verificar si el usuario tiene una sesión activa
// Requisito profesor: gestión de sesiones con isset() y comentarios descriptivos

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session (gestión de sesiones)
require_once '../Config/Session.php';

// 3. Validación del método HTTP (requisito profesor: códigos HTTP correctos)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use GET para verificar la sesión.',
        'data' => null
    ]);
    exit;
}

// 4. Verificación de sesión activa usando isset()
//    $_SESSION['user'] contiene los datos del usuario autenticado
//    Si existe, el usuario tiene una sesión válida y podemos devolver sus datos
if (isset($_SESSION['user'])) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Sesión activa y válida.',
        'data' => ['user' => $_SESSION['user']]
    ]);
} else {
    // No hay sesión activa: el usuario no está autenticado
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No existe una sesión activa.',
        'data' => null
    ]);
}
?>
