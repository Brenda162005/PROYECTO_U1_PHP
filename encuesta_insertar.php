<?php
require "model_layer/EncuestaService.php";
header("Content-Type: application/json");

$service = new EncuestaService();

// Recibimos los datos simples del formulario
$titulo = $_POST['titulo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
// Convertir el string "true"/"false" de Java a booleano real
$publicada = ($_POST['esta_publicada'] === 'true'); 

// Recibir el JSON con las preguntas
$preguntasJson = $_POST['preguntas_json'] ?? '[]'; 

// Recibir el archivo de imagen (si se envió)
$imagen = $_FILES['imagen'] ?? null;


$resultado = $service->crearEncuestaCompleta($titulo, $descripcion, $publicada, $imagen, $preguntasJson);

if ($resultado) {
    echo json_encode(["success" => "Encuesta creada exitosamente"]);
} else {
    echo json_encode(["error" => "Error al guardar la encuesta"]);
}
?>