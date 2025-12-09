<?php
require "model_layer/EncuestaService.php";
header("Content-Type: application/json");

$service = new EncuestaService();

$titulo = $_POST['titulo'] ?? '';

if (!empty($titulo)) {
    $resultado = $service->publicarEncuesta($titulo);

    if ($resultado) {
        echo json_encode(["success" => "Encuesta publicada correctamente"]);
    } else {
        echo json_encode(["error" => "No se encontró la encuesta o ya estaba publicada"]);
    }
} else {
    echo json_encode(["error" => "Falta datos"]);
}
?>