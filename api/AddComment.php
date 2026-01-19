<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../Config/Database.php';
include_once '../model/dao/CommentDAO.php';

// 1. CORRECCIÓN: Creamos la instancia del DAO (Importante: new CommentDAO)
$commentDAO = new CommentDAO();

// 2. Leemos los datos enviados por Javascript
$input_raw = file_get_contents("php://input");
$data = json_decode($input_raw);

// Comprobamos si el JSON ha llegado roto o vacío
if ($data === null) {
    http_response_code(400);
    echo json_encode([
        "message" => "Error: No se ha recibido ningún JSON válido.",
        "debug_raw" => $input_raw // Te mostrará qué llegó realmente
    ]);
    exit();
}

// 3. Verificamos los 4 datos obligatorios
if (
    !empty($data->profileCode) &&
    !empty($data->isbn) &&
    !empty($data->comment) &&
    !empty($data->valoration)
) {
    // 4. CORRECCIÓN: Usamos la variable $commentDAO que creamos arriba
    $fecha = $data->date ?? date('Y-m-d'); // Si no viene fecha, usa hoy

    if ($commentDAO->createComment(
        $data->profileCode,
        $data->isbn,
        $data->comment,
        $data->valoration,
        $fecha
    )) {
        http_response_code(201);
        echo json_encode(array("message" => "Review posted successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to post review. SQL Error."));
    }
} else {
    // 5. MODO DETECTIVE: Esto te dirá exactamente qué campo falta en la consola
    http_response_code(400);
    echo json_encode(array(
        "message" => "Incomplete data. Faltan datos obligatorios.",
        "debug_missing" => [
            "profileCode" => !empty($data->profileCode) ? "OK ({$data->profileCode})" : "FALTA",
            "isbn"        => !empty($data->isbn) ? "OK" : "FALTA",
            "comment"     => !empty($data->comment) ? "OK" : "FALTA",
            "valoration"  => !empty($data->valoration) ? "OK" : "FALTA"
        ]
    ));
}
?>