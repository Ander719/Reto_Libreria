<?php
// api/Logout.php
header('Content-Type: application/json');

require_once '../controller/ProfileController.php';

$auth = new ProfileController();
$response = $auth->logout();

echo json_encode($response);
?>