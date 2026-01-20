<?php
// api/ModifyBook.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/BookController.php';
require_once '../model/dao/AuthorDAO.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'error' => 'Método incorrecto.']);
    exit;
}

// Datos texto
$isbn = $_POST['isbn'] ?? '';
$title = $_POST['title'] ?? '';
$authorName = $_POST['authorName'] ?? '';
$authorSurname = $_POST['authorSurname'] ?? '';
$pages = $_POST['pages'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$synopsis = $_POST['synopsis'] ?? '';
$price = $_POST['price'] ?? 0;
$editorial = $_POST['editorial'] ?? '';

// --- GESTIÓN DE PORTADA ---
// 'cover' trae el nombre que está actualmente en la base de datos (gracias al JS corregido)
$oldCoverName = $_POST['cover'] ?? ''; 
$finalCoverName = $oldCoverName; // Por defecto, si no suben nada, se queda la vieja

// Verificar si suben archivo nuevo
if (isset($_FILES['coverFile']) && $_FILES['coverFile']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['coverFile'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($extension, $allowed_extensions)) {
        $uploadDir = dirname(__DIR__) . '/view/assets/img/covers/';
        
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // Nuevo nombre único
        $newFileName = 'cover_' . time() . '_' . rand(100, 999) . '.' . $extension;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // ¡ÉXITO SUBIENDO LA NUEVA! -> Ahora procedemos a borrar la vieja
            $finalCoverName = $newFileName;

            // Lógica de borrado:
            // 1. Que exista un nombre viejo.
            // 2. Que el nombre viejo sea diferente al "default.jpg" (si usas uno genérico).
            // 3. Que el archivo exista físicamente.
            if (!empty($oldCoverName) && $oldCoverName !== 'default.jpg') {
                $oldFilePath = $uploadDir . $oldCoverName;
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath); // <--- Aquí se borra la imagen antigua
                }
            }

        } else {
            echo json_encode(['exito' => false, 'error' => 'Error al guardar imagen en servidor.']);
            exit;
        }
    } else {
        echo json_encode(['exito' => false, 'error' => 'Formato de imagen no válido.']);
        exit;
    }
}

// --- GESTIÓN DE AUTOR ---
$authorDAO = new AuthorDAO();
$authorId = $authorDAO->getOrCreateAuthorId($authorName, $authorSurname);

if (!$authorId) {
    echo json_encode(['exito' => false, 'error' => 'Error al gestionar el autor.']);
    exit;
}

// --- GUARDAR EN BASE DE DATOS ---
$controller = new BookController();
$result = $controller->modifyBook(
    $isbn, 
    $title, 
    $authorId, 
    $pages, 
    $stock, 
    $synopsis, 
    $price, 
    $editorial, 
    $finalCoverName
);

if ($result) {
    echo json_encode(['exito' => true, 'message' => 'Libro modificado correctamente', 'newCover' => $finalCoverName]);
} else {
    echo json_encode(['exito' => false, 'error' => 'Error al actualizar BD']);
}
?>