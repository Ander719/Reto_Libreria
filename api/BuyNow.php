<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../controller/controller.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!empty($input['profileCode']) && !empty($input['isbn']) && !empty($input['quantity'])) {
    
    $controller = new controller();
    // Esta función la añadiste antes al controller.php
    $result = $controller->createDirectOrder(
        $input['profileCode'],
        $input['isbn'],
        $input['quantity']
    );

    if ($result) {
        echo json_encode(['exito' => true, 'message' => 'Compra realizada con éxito.']);
    } else {
        echo json_encode(['exito' => false, 'error' => 'No se pudo procesar la compra (BD Error).']);
    }

} else {
    echo json_encode(['exito' => false, 'error' => 'Datos incompletos.']);
}
?>