<?php
// api/CheckSession.php
require_once "../Config/Session.php";
require_once "../Config/Database.php"; 

header("Content-Type: application/json; charset=utf-8");

// Modelos necesarios
require_once '../model/entities/Profile.php';
require_once '../model/entities/User.php';
require_once '../model/entities/Admin.php';

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $userData = $user;

    // Convertir objeto a array de forma segura
    if (is_object($user)) {
        if (method_exists($user, 'toArray')) {
            $userData = $user->toArray();
        } else {
            $userData = (array)$user;
        }
    }

    // --- DETECCIÓN ADMIN (VERSIÓN PDO) ---
    $isAdmin = false;
    $id = is_object($user) ? ($user->profile_code ?? null) : ($user['profile_code'] ?? null);

    if ($id) {
        $db = new Database();
        $conn = $db->getConnection(); // Esto devuelve un objeto PDO
        
        // Consulta SQL con PDO
        $sql = "SELECT profile_code FROM admin_ WHERE profile_code = :id";
        $stmt = $conn->prepare($sql);
        
        // Ejecutamos pasando el parámetro (más fácil que bind_param)
        $stmt->execute([':id' => $id]);
        
        // fetchColumn() devuelve true si encuentra algo, false si no
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $isAdmin = true;
        }
    }

    // Añadir isAdmin a la respuesta
    if (is_array($userData)) {
        $userData['isAdmin'] = $isAdmin;
    } elseif (is_object($userData)) {
        $userData->isAdmin = $isAdmin;
    }
    // -------------------------------------

    echo json_encode(["success" => true, "user" => $userData]);

} else {
    echo json_encode(["success" => false, "error" => "No hay sesión activa"]);
}
?>