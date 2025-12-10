<?php
require "model_layer/UsuarioService.php";
header("Content-Type: application/json");

$service = new UsuarioService();
$nombre = $_POST['usuario'];
$pass = $_POST['password'];
$esAdmin = $_POST['es_admin']; 
$archivo = $_FILES['imagen'] ?? null;
$resultado = $service->registrarUsuario($nombre, $pass, $esAdmin, $archivo);

if ($resultado) {
    echo json_encode(["success" => "Usuario registrado correctamente"]);
} else {
    echo json_encode(["error" => "No se pudo registrar (¿Usuario ya existe?)"]);
}
?>