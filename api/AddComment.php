<?php
// Cabeceras para permitir que JS lea la respuesta (IL8.2 Respuestas Dinámicas)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../Config/db.php'; // Tu archivo de conexión
include_once '../model/CommentModel.php';

// 1. Instanciar base de datos y modelo
$database = new Database(); // Ajusta esto según cómo te llames a tu clase de conexión
$db = $database->getConnection();
$commentModel = new CommentModel($db);

// 2. Obtener los datos enviados por el JS (JSON)
$data = json_decode(file_get_contents("php://input"));

// 3. Validar que los datos no vengan vacíos (Seguridad básica)
if (
    !empty($data->profileCode) &&
    !empty($data->isbn) &&
    !empty($data->comment) &&
    !empty($data->valoration)
) {
    // 4. Intentar guardar
    if ($commentModel->createComment(
        $data->profileCode,
        $data->isbn,
        $data->comment,
        $data->valoration,
        $data->date
    )) {
        // Respuesta 201: Creado con éxito
        http_response_code(201);
        echo json_encode(array("message" => "Review posted successfully."));
    } else {
        // Respuesta 503: Error del servidor
        http_response_code(503);
        echo json_encode(array("message" => "Unable to post review. SQL Error."));
    }
} else {
    // Respuesta 400: Datos incompletos
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}
?>