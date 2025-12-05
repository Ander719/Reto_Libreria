<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../controller/controller.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$pswd1 = $input['pswd1'] ?? '';
$pswd2 = $input['pswd2'] ?? '';

$response = ["exito" => false];

try {

    $controller = new controller();
    $user = $controller->create_user($username, $pswd1);

    if ($user) {
        echo json_encode([
            'resultado' => $user,
            'exito' => true
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'error' => 'No se ha creado correctamente el usuario',
            'exito' => false
        ]);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'exito' => false
    ]);
}
?>

