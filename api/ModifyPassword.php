<?php
session_start(); 
header('Content-Type: application/json; charset=utf-8');
require_once '../model/dao/ProfileDAO.php';

$input = json_decode(file_get_contents('php://input'), true);
$profile_code = $input['profile_code'] ?? '';
$password = $input['password'] ?? '';

    if (empty($profile_code) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$dao = new ProfileDAO();
$modify = $dao->modifyPassword($profile_code, $passwordHash);

    if ($modify) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Contraseña modificada correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al modificar la contraseña']);
    }
?>