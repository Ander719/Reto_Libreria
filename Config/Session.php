<?php

// Varios PHP incluyen este archivo; la sesion solo debe arrancar una vez.
if (session_status() === PHP_SESSION_NONE) {
    // Cookies de sesion para entorno local, con HttpOnly y SameSite activos.
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
