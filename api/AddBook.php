<?php
// api/AddBook.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Ajusta la ruta si tu estructura es diferente
require_once '../controller/controller.php';

// 1. VALIDACIÓN DE MÉTODO
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'error' => 'Método no permitido. Se requiere POST.']);
    exit;
}

// 2. RECOGIDA DE DATOS
$isbn = $_POST['isbn'] ?? '';
$title = $_POST['title'] ?? '';
$authorName = $_POST['authorName'] ?? '';
$authorSurname = $_POST['authorSurname'] ?? '';
$pages = $_POST['pages'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$synopsis = $_POST['synopsis'] ?? '';
$price = $_POST['price'] ?? 0;
$editorial = $_POST['editorial'] ?? '';

// 3. VALIDACIÓN DE CAMPOS OBLIGATORIOS
if (empty($isbn) || empty($title) || empty($authorName)) {
    echo json_encode(['exito' => false, 'error' => 'Error: ISBN, Título y Nombre de Autor son obligatorios.']);
    exit;
}

// 4. GESTIÓN DE LA IMAGEN (FILE UPLOAD)
$coverName = "default.jpg"; // Valor por defecto

if (isset($_FILES['coverFile']) && $_FILES['coverFile']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['coverFile'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($extension, $allowed_extensions)) {
        // Usamos __DIR__ para obtener la ruta absoluta y evitar errores de "carpeta no encontrada"
        // Subimos un nivel (api -> raiz) y entramos en view/assets/img/covers/
        $uploadDir = __DIR__ . '/../view/assets/img/covers/';
        
        // Crear carpeta si no existe
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                echo json_encode(['exito' => false, 'error' => 'Error: No se pudo crear el directorio de portadas.']);
                exit;
            }
        }

        // Generar nombre único
        $newFileName = 'cover_' . time() . '_' . rand(100, 999) . '.' . $extension;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $coverName = $newFileName;
        } else {
            echo json_encode(['exito' => false, 'error' => 'Error al mover el archivo subido. Verifica permisos.']);
            exit;
        }
    } else {
        echo json_encode(['exito' => false, 'error' => 'Formato de imagen no permitido (solo jpg, png, webp).']);
        exit;
    }
}

// 5. LLAMADA AL CONTROLADOR
$controller = new controller();

// Pasamos los datos limpios. Nota que $coverName es un string (el nombre del archivo)
$response = $controller->createBook(
    $isbn, 
    $title, 
    $authorName, 
    $authorSurname, 
    $pages, 
    $stock, 
    $synopsis, 
    $price, 
    $editorial, 
    $coverName
);

// 6. RESPUESTA AL CLIENTE
if ($response) {
    echo json_encode(['exito' => true, 'message' => 'Libro creado correctamente.']);
} else {
    echo json_encode(['exito' => false, 'error' => 'Error al guardar en base de datos. Posible ISBN duplicado.']);
}
?>