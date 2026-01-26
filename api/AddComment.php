<?php
// api/AddComment.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../Config/Database.php';
include_once '../model/dao/CommentDAO.php';

// Desactivar errores visibles para asegurar JSON limpio
error_reporting(0);
ini_set('display_errors', 0);

// 1. Instanciar DAO
$commentDAO = new CommentDAO();

// 2. Leer datos
$input_raw = file_get_contents("php://input");
$data = json_decode($input_raw);

// Comprobamos JSON
if ($data === null) {
    http_response_code(400);
    echo json_encode(["message" => "Error: JSON inválido.", "debug_raw" => $input_raw]);
    exit();
}

// 3. Verificamos datos obligatorios
if (
    !empty($data->profileCode) &&
    !empty($data->isbn) &&
    !empty($data->comment) &&
    !empty($data->valoration)
) {
    
    // =======================================================================
    // 🛡️ BLOQUE DE SEGURIDAD: VERIFICAR SI ES ADMIN
    // =======================================================================
    $db = new Database();
    $conn = $db->getConnection();
    
    // Consultamos si este profileCode existe en la tabla admin_
    $sqlAdmin = "SELECT profile_code FROM admin_ WHERE profile_code = :id";
    $stmtAdmin = $conn->prepare($sqlAdmin);
    $stmtAdmin->execute([':id' => $data->profileCode]);
    
    if ($stmtAdmin->fetch(PDO::FETCH_ASSOC)) {
        // ¡ES UN ADMIN! -> Prohibido pasar
        http_response_code(403);
        echo json_encode(["message" => "Acción denegada: Los administradores no pueden publicar reseñas."]);
        exit(); // Detenemos el script aquí mismo
    }
    // =======================================================================

    // 4. Si no es admin, procedemos a crear el comentario
    $fecha = $data->date ?? date('Y-m-d');

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
        echo json_encode(array("message" => "Unable to post review. SQL Error or Duplicate."));
    }

} else {
    // 5. Datos incompletos
    http_response_code(400);
    echo json_encode(array(
        "message" => "Incomplete data.",
        "debug_missing" => [
            "profileCode" => !empty($data->profileCode) ? "OK" : "FALTA",
            "isbn"        => !empty($data->isbn) ? "OK" : "FALTA",
            "comment"     => !empty($data->comment) ? "OK" : "FALTA",
            "valoration"  => !empty($data->valoration) ? "OK" : "FALTA"
        ]
    ));
}
?>