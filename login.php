<?php
require "model_layer/UsuarioService.php";
header("Content-Type: application/json");

$service = new UsuarioService();

$usr = $_POST['usuario'] ?? '';
$pass = $_POST['password'] ?? '';

$user = $service->login($usr, $pass);

if ($user) {
    echo json_encode($user);
} else {
    echo json_encode(["error" => "Credenciales incorrectas"]);
}
?>