<?php
require "model_layer/EncuestaService.php";
header("Content-Type: application/json");

$service = new EncuestaService();


$titulo = $_POST['titulo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';

$publicada = ($_POST['esta_publicada'] === 'true'); 


$preguntasJson = $_POST['preguntas_json'] ?? '[]'; 


$imagen = $_FILES['imagen'] ?? null;


$resultado = $service->crearEncuestaCompleta($titulo, $descripcion, $publicada, $imagen, $preguntasJson);

if ($resultado) {
    echo json_encode(["success" => "Encuesta creada exitosamente"]);
} else {
    echo json_encode(["error" => "Error al guardar la encuesta"]);
}
?>