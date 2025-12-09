<?php
require "model_layer/EncuestaService.php";
header("Content-Type: application/json");

$service = new EncuestaService();

// Java envía el parámetro 'titulo'
$titulo = $_POST['titulo'] ?? '';

if (!empty($titulo)) {
    $resultado = $service->borrarEncuesta($titulo);

    if ($resultado) {
        echo json_encode(["success" => "Encuesta eliminada"]);
    } else {
        echo json_encode(["error" => "No se encontró la encuesta o tiene respuestas asociadas"]);
    }
} else {
    echo json_encode(["error" => "Falta el titulo"]);
}
?>