<?php
// api/DeleteComment.php

// Incluir los archivos necesarios para acceder a la base de datos y controladores
require_once '../Config/Database.php';
require_once '../controller/CommentController.php';

// Configurar la respuesta como JSON
header('Content-Type: application/json');

$response = ['exito' => false, 'error' => ''];

try {
    // Verificar si se ha recibido el ID del comentario por POST
    if (isset($_POST['comment_id'])) {
        $commentId = $_POST['comment_id'];

        // Instanciar el controlador de comentarios
        $commentController = new CommentController();

        // Ejecutar la eliminación
        // Se asume que tu controlador tiene un método deleteComment o similar
        $resultado = $commentController->deleteComment($commentId);

        if ($resultado) {
            $response['exito'] = true;
        } else {
            $response['error'] = "No se pudo eliminar el comentario de la base de datos.";
        }
    } else {
        $response['error'] = "ID de comentario no proporcionado.";
    }
} catch (Exception $e) {
    $response['error'] = "Error en el servidor: " . $e->getMessage();
}

// Devolver la respuesta al JavaScript
echo json_encode($response);