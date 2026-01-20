<?php
// api/DeleteUser.php
session_start();
header('Content-Type: application/json');

// --- CORRECCIÓN: Usamos ProfileController ---
require_once '../controller/ProfileController.php';

if (!isset($_GET['id'])) {
    echo json_encode(['result' => false, 'error' => 'No ID provided']);
    exit;
}

$idToDelete = $_GET['id'];
$controller = new ProfileController();

$result = $controller->delete_user($idToDelete);

if ($result) {
    $isSelfDelete = (isset($_SESSION['user']['profile_code']) && $_SESSION['user']['profile_code'] == $idToDelete);
    
    if ($isSelfDelete) {
        session_destroy();
    }

    echo json_encode([
        'result' => true, 
        'isSelfDelete' => $isSelfDelete 
    ]);
} else {
    echo json_encode(['result' => false, 'error' => 'Error in DB']);
}
?>