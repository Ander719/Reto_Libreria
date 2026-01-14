<?php
// api/DeleteUser.php
session_start();
header('Content-Type: application/json');
require_once '../controller/controller.php';

// 1. Verificar si hay ID
if (!isset($_GET['id'])) {
    echo json_encode(['result' => false, 'error' => 'No ID provided']);
    exit;
}

$idToDelete = $_GET['id'];
$controller = new controller();

// 2. Ejecutar borrado
$result = $controller->delete_user($idToDelete);

if ($result) {
    // 3. Lógica de Auto-Borrado
    // Si el ID que acabo de borrar coincide con mi ID de sesión, cierro sesión.
    $isSelfDelete = (isset($_SESSION['profile_code']) && $_SESSION['profile_code'] == $idToDelete);
    
    if ($isSelfDelete) {
        session_destroy();
    }

    echo json_encode([
        'result' => true, 
        'isSelfDelete' => $isSelfDelete // Avisamos al JS para que redirija
    ]);
} else {
    echo json_encode(['result' => false, 'error' => 'Error in DB']);
}
?>