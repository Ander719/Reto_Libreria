<?php
require_once "../Config/Session.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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
