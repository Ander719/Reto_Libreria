<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../controller/OrderController.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!empty($input['profileCode']) && !empty($input['isbn']) && !empty($input['quantity'])) {
    
    $controller = new OrderController();
    $result = $controller->createDirectOrder(
        $input['profileCode'],
        $input['isbn'],
        $input['quantity']
    );

    if ($result === true) {
        echo json_encode(['exito' => true, 'message' => 'Compra realizada con éxito.']);
    } elseif ($result === "NO_STOCK") {
        echo json_encode(['exito' => false, 'error' => 'Error: No hay suficiente stock...']);
    } else {
        echo json_encode(['exito' => false, 'error' => 'No se pudo procesar la compra (BD Error).']);
    }

} else {
    echo json_encode(['exito' => false, 'error' => 'Datos incompletos.']);
}
?>