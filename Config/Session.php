<?php
// Evitamos iniciarla dos veces
if (session_status() === PHP_SESSION_NONE) {
    // Configuración ROBUSTA y UNIFICADA
    session_set_cookie_params([
        'path' => '/',            
        'lifetime' => 0,          
        'secure' => false,        // false para localhost
        'httponly' => true,       
        'samesite' => 'Lax'       
    ]);
    session_start();
}
?>