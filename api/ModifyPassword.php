<?php
// api/ModifyPassword.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

// IMPORTANTE: Llamamos al DAO directamente por si el Controller no tiene el método activado
require_once '../model/dao/ProfileDAO.php';

$input = json_decode(file_get_contents('php://input'), true);
$profile_code = $input['profile_code'] ?? '';
$password = $input['password'] ?? '';

if (empty($profile_code) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// 1. ENCRIPTAR LA CONTRASEÑA (¡Vital!)
// El nuevo sistema usa password_verify, así que debemos guardar el hash.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// 2. GUARDAR
$dao = new ProfileDAO();
$modify = $dao->modifyPassword($profile_code, $passwordHash);

if ($modify) {
    echo json_encode(['success' => true, 'message' => 'Contraseña modificada correctamente']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al modificar la contraseña']);
}
?>