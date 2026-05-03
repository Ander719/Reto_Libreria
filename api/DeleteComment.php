<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../controller/CommentController.php';

error_reporting(0);
ini_set('display_errors', 0);

$data = json_decode(file_get_contents("php://input"));
$controller = new CommentController();

if (!empty($data->isbn) && !empty($data->profileCode)) {
    $isbn = trim(htmlspecialchars((string)$data->isbn));
    $profileCode = trim(htmlspecialchars((string)$data->profileCode));

    if ($isbn === '' || $profileCode === '') {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'code' => 400,
            'message' => 'Faltan datos.',
            'data' => null
        ]);
        exit;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['user'])) {
        $response = $controller->deleteComment($isbn, $profileCode, $_SESSION['user']);
        
        http_response_code($response["code"]);
        echo json_encode([
            'status' => $response["success"] ? 'success' : 'error',
            'code' => $response["code"],
            'message' => $response["message"],
            'data' => null
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'code' => 401,
            'message' => 'Debes iniciar sesión.',
            'data' => null
        ]);
    }

} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'message' => 'Faltan datos.',
        'data' => null
    ]);
}
?>
