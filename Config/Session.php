<?php
// Config/Session.php — Configuración robusta y centralizada de sesiones para la aplicación
// Este archivo debe ser incluido en todo endpoint o script que requiera persistencia de estado

if (session_status() === PHP_SESSION_NONE) {
    // Definimos parámetros de cookie para aumentar la seguridad contra ataques XSS y secuestro de sesión
    session_set_cookie_params([
        'lifetime' => 0,          // La sesión expira al cerrar el navegador
        'path' => '/',            // Disponible en todo el dominio
        'secure' => false,        // Cambiar a true en entornos de producción con HTTPS
        'httponly' => true,       // Para que no se pueda leer por JS
        'samesite' => 'Lax'       // Protección contra CSRF
        ]);

    // Iniciamos la sesión en el servidor
    if (!session_start()) {
        // En caso de fallo crítico al iniciar la sesión, registramos el error
        error_log("Error crítico: No se pudo iniciar la sesión en el servidor.");
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "status" => "error",
            "code" => 500,
            "message" => "Fallo interno al inicializar el sistema de sesiones.",
            "data" => null
        ]);
        exit;
    }
}
?>