<?php
// api/CheckSession.php
require_once "../config/Session.php";
header("Content-Type: application/json; charset=utf-8");

// --- ¡ESTAS SON LAS LÍNEAS QUE FALTAN! ---
// Sin esto, PHP no entiende el objeto de la sesión y toArray() falla.
require_once '../model/entities/Profile.php';
require_once '../model/entities/User.php';
require_once '../model/entities/Admin.php'; // Si tienes admins
// ------------------------------------------

// Verificamos si existe la variable 'user' en la sesión
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];

    $userData = $user;

    if (is_object($user) && method_exists($user, 'toArray')) {
        $userData = $user->toArray();
    } elseif (is_object($user)) {
        session_destroy();
        echo json_encode(["success" => false, "error" => "Sesión antigua incompatible. Recarga."]);
        exit();
    }

    echo json_encode([
        "success" => true,
        "user" => $userData
    ]);

} else {
    // No hay sesión
    echo json_encode([
        "success" => false,
        "error" => "No hay sesión activa"
    ]);
}
?>