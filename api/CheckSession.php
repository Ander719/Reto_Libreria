<?php
require_once "../Config/Session.php"; //

header("Content-Type: application/json; charset=utf-8");

if (isset($_SESSION['user'])) {
    
    echo json_encode([
        "success" => true, 
        "user" => $_SESSION['user']
    ]);

} else {
    echo json_encode([
        "success" => false, 
        "error" => "No hay sesión activa"
    ]);
}
