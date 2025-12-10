<?php
require "model_layer/Conexion.php";
header("Content-Type: application/json; charset=utf-8");

$conexionObj = new Conexion();
$con = $conexionObj->open();
$con->set_charset("utf8mb4");

$idEncuesta = isset($_POST['id_encuesta']) ? $_POST['id_encuesta'] : 0;
$resultados = [];

if ($idEncuesta > 0) {
    
    $mapaPreguntas = [];
    $sqlPreguntas = "SELECT p.id, p.texto_pregunta 
                     FROM preguntas p 
                     WHERE p.id_encuesta = ? 
                     AND NOT EXISTS (
                         SELECT 1 FROM preguntas sub WHERE sub.id_pregunta_padre = p.id
                     )";

    $stmtP = $con->prepare($sqlPreguntas);
    $stmtP->bind_param("i", $idEncuesta);
    $stmtP->execute();
    $resP = $stmtP->get_result();
    
    while ($rowP = $resP->fetch_assoc()) {
        $mapaPreguntas[$rowP['id']] = [
            "texto" => $rowP['texto_pregunta'],
            "votos" => [0, 0, 0, 0, 0] 
        ];
    }
    $stmtP->close();

    
    $sqlVotos = "SELECT rd.id_pregunta, rd.puntuacion 
                 FROM respuestas_detalle rd 
                 JOIN respuestas_encabezado re ON rd.id_respuesta_encabezado = re.id 
                 WHERE re.id_encuesta = ?"; 
    
    $stmtV = $con->prepare($sqlVotos);
    $stmtV->bind_param("i", $idEncuesta);
    $stmtV->execute();
    $resV = $stmtV->get_result();

    while ($rowV = $resV->fetch_assoc()) {
        $idPreg = $rowV['id_pregunta'];
        $puntos = (int)$rowV['puntuacion'];

        
        if (isset($mapaPreguntas[$idPreg]) && $puntos >= 1 && $puntos <= 5) {
            $mapaPreguntas[$idPreg]['votos'][$puntos - 1]++;
        }
    }
    $stmtV->close();

    foreach ($mapaPreguntas as $id => $datos) {
        $claveUnica = $datos['texto']; 
        $resultados[$claveUnica] = $datos['votos'];
    }
}

echo json_encode($resultados);
$conexionObj->close($con);
?>