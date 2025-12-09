<?php
require "model_layer/EncuestaService.php";
header("Content-Type: application/json");

$service = new EncuestaService();

// Obtenemos la lista de encuestas (solo encabezados, sin preguntas detalladas)
$lista = $service->getEncuestas();

// Devolvemos el JSON limpio
echo json_encode($lista);
?>