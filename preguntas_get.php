<?php
require "model_layer/Conexion.php";
header("Content-Type: application/json; charset=utf-8");

$conexionObj = new Conexion();
$con = $conexionObj->open();
$con->set_charset("utf8mb4");

$idEncuesta = isset($_GET['id_encuesta']) ? $_GET['id_encuesta'] : null;
$listaFinal = [];

if ($idEncuesta) {
   
    $sql = "SELECT id, id_encuesta, texto_pregunta, id_pregunta_padre 
            FROM preguntas 
            WHERE id_encuesta = ?";
    
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $idEncuesta);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $todasLasPreguntas = [];

    
    while ($row = $result->fetch_assoc()) {
        $p = [
            "id" => (int)$row['id'],
            "id_encuesta" => (int)$row['id_encuesta'],
            "texto_pregunta" => $row['texto_pregunta'],
            "id_pregunta_padre" => $row['id_pregunta_padre'] ? (int)$row['id_pregunta_padre'] : null,
            "sub_preguntas" => [] 
        ];
        $todasLasPreguntas[$p['id']] = $p;
    }

    
    foreach ($todasLasPreguntas as $id => &$pregunta) { 
        if ($pregunta['id_pregunta_padre'] != null) {
            $idPadre = $pregunta['id_pregunta_padre'];
            if (isset($todasLasPreguntas[$idPadre])) {
                $todasLasPreguntas[$idPadre]['sub_preguntas'][] = &$pregunta;
            }
        }
    }
    
    unset($pregunta); 

    
    foreach ($todasLasPreguntas as $id => $pregunta) {
        if ($pregunta['id_pregunta_padre'] == null) {
            $listaFinal[] = $pregunta;
        }
    }
}

echo json_encode($listaFinal);
$conexionObj->close($con);
?>