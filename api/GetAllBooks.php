<?php
// api/GetAllBooks.php — Endpoint para obtener el catálogo completo de libros
// Requisito profesor: códigos HTTP correctos, respuestas JSON estandarizadas

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session, Database, Controller
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/BookController.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use GET para consultar el catálogo.',
        'data' => null
    ]);
    exit;
}

// 4. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new BookController($db);

// 5. Obtención del catálogo completo mediante procedimiento almacenado
$books = $controller->getAllBooks();

// 6. Respuesta de éxito con código 200
//    Si no hay libros, devolvemos un array vacío para mantener consistencia
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'code' => 200,
    'message' => 'Catálogo recuperado correctamente.',
    'data' => ['books' => $books ?: []]
]);
?>
