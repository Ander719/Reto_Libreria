<?php
// api/DeleteUser.php
session_start();
header('Content-Type: application/json');
require_once '../controller/ProfileController.php';

// Leer el JSON enviado por el JS
$data = json_decode(file_get_contents("php://input"), true);
$idToDelete = $data['id'] ?? null;

if (!$idToDelete) {
    echo json_encode(['success' => false, 'error' => 'No ID provided']); //
    exit;
}

$controller = new ProfileController();
$result = $controller->delete_user($idToDelete); //

if ($result) {
    $isSelfDelete = (isset($_SESSION['user']['profile_code']) && $_SESSION['user']['profile_code'] == $idToDelete);
    if ($isSelfDelete) {
        session_destroy(); //
    }
    echo json_encode(['success' => true, 'isSelfDelete' => $isSelfDelete]); //
} else {
    echo json_encode(['success' => false, 'error' => 'Error al eliminar en la BD']);
}
?>