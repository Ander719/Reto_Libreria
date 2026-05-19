<?php
// api/ModifyBook.php — Endpoint para modificar un libro existente (solo admin)
// Requisito profesor: validaciones server-side, gestión de sesiones, códigos HTTP

// 1. Header Content-Type (siempre antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');

// 2. Requires: Session (auth), Database, Controller
require_once '../Config/Session.php';
require_once '../Config/Database.php';
require_once '../controller/BookController.php';
require_once '../model/entities/Book.php';

// 3. Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'message' => 'Método no permitido. Use POST para modificar un libro.',
        'data' => null
    ]);
    exit;
}

// 4. Verificación de sesión activa
if (!isset($_SESSION['user']) || empty($_SESSION['user']['profile_code'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 401,
        'message' => 'No autorizado. Debes iniciar sesión.',
        'data' => null
    ]);
    exit;
}

// 5. Verificación de rol administrador
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'code' => 403,
        'message' => 'Acceso restringido. Se requieren privilegios de administrador.',
        'data' => null
    ]);
    exit;
}

// 6. Recogida de datos del formulario
$isbn = isset($_POST['isbn']) ? trim($_POST['isbn']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$authorName = isset($_POST['authorName']) ? trim($_POST['authorName']) : '';
$authorSurname = isset($_POST['authorSurname']) ? trim($_POST['authorSurname']) : '';
$pages = isset($_POST['pages']) ? $_POST['pages'] : 0;
$stock = isset($_POST['stock']) ? $_POST['stock'] : 0;
$synopsis = isset($_POST['synopsis']) ? trim($_POST['synopsis']) : '';
$price = isset($_POST['price']) ? $_POST['price'] : 0;
$editorial = isset($_POST['editorial']) ? trim($_POST['editorial']) : '';
$oldCover = isset($_POST['cover']) ? trim($_POST['cover']) : 'default.jpg';

// 7. Saneamiento de textos para prevenir XSS
$isbn = htmlspecialchars($isbn, ENT_QUOTES, 'UTF-8');
$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
$authorName = htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8');
$authorSurname = htmlspecialchars($authorSurname, ENT_QUOTES, 'UTF-8');
$synopsis = htmlspecialchars($synopsis, ENT_QUOTES, 'UTF-8');
$editorial = htmlspecialchars($editorial, ENT_QUOTES, 'UTF-8');

// 8. Validación de tipos numéricos
$pages = filter_var($pages, FILTER_VALIDATE_INT);
$stock = filter_var($stock, FILTER_VALIDATE_INT);
$price = filter_var($price, FILTER_VALIDATE_FLOAT);

// 9. Validación server-side: campos obligatorios
if (empty($isbn) || empty($title) || empty($authorName) || $pages === false || $stock === false || $price === false) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Datos de entrada no válidos. Complete todos los campos obligatorios.',
        'data' => null
    ]);
    exit;
}

// 10. Validación de formato ISBN
if (!preg_match('/^\d{13}$/', $isbn)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'El ISBN debe contener exactamente 13 dígitos numéricos.',
        'data' => null
    ]);
    exit;
}

// 11. Validación de rangos
if ($pages <= 0 || $stock < 0 || $price <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Valores numéricos fuera de rango. Páginas y precio deben ser > 0, stock >= 0.',
        'data' => null
    ]);
    exit;
}

// 12. Procesamiento del archivo de portada (si se sube uno nuevo)
$finalCover = $oldCover;
if (isset($_FILES['coverFile']) && $_FILES['coverFile']['error'] === UPLOAD_ERR_OK) {
    $maxSize = 2 * 1024 * 1024;
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    $fileSize = $_FILES['coverFile']['size'];
    $tmpPath = $_FILES['coverFile']['tmp_name'];
    $originalName = $_FILES['coverFile']['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMimeType = $finfo ? finfo_file($finfo, $tmpPath) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    if ($fileSize <= 0 || $fileSize > $maxSize || !in_array($extension, $allowedExtensions, true) || !in_array($realMimeType, $allowedMimeTypes, true)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'code' => 400,
            'message' => 'Archivo de portada no válido (máx 2MB, formatos: JPG, PNG, WEBP).',
            'data' => null
        ]);
        exit;
    }

    $uploadDir = '../view/assets/img/covers/';
    $newFileName = 'cover_' . time() . '_' . rand(100, 999) . '.' . $extension;

    if (move_uploaded_file($tmpPath, $uploadDir . $newFileName)) {
        $finalCover = $newFileName;
        // Limpieza de la portada anterior para optimizar espacio en disco
        if ($oldCover !== 'default.jpg' && file_exists($uploadDir . $oldCover)) {
            @unlink($uploadDir . $oldCover);
        }
    }
}

// 13. Conexión a BD y obtención del controlador
$database = new Database();
$db = $database->getConnection();
$controller = new BookController($db);

// 14. Obtención o creación del autor
$authorId = $controller->getOrCreateAuthor($authorName, $authorSurname);

if (!$authorId) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al procesar la información del autor.',
        'data' => null
    ]);
    exit;
}

// 15. Creación del objeto entidad Book para la actualización
$bookToUpdate = new Book($title, $authorId, $isbn, $pages, $stock, $synopsis, $price, $editorial, $finalCover);

// 16. Actualización del libro en la base de datos
$result = $controller->modifyBook($bookToUpdate);

// 17. Respuesta según resultado
if ($result) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Información del libro actualizada correctamente.',
        'data' => ['isbn' => $isbn]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Error al intentar actualizar el libro en la base de datos.',
        'data' => null
    ]);
}
?>
