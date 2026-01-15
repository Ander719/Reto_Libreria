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



$controller = new controller();
// Esto ahora devuelve un objeto User gracias al DAO
$newUserObj = $controller->create_user($username, $pswd1);

if ($newUserObj) {
    echo json_encode([
        'exito' => true,
        // Convertimos el objeto a array para enviarlo
        'resultado' => $newUserObj->toArray()
    ]);
} else {
    echo json_encode([
        'exito' => false,
        'error' => 'No se ha podido crear el usuario (¿Quizás ya existe?)'
    ]);
}
