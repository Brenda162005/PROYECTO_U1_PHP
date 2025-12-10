<?php
require "model_layer/Conexion.php";
header("Content-Type: application/json; charset=utf-8");

$conexionObj = new Conexion();
$con = $conexionObj->open();
$con->set_charset("utf8mb4");

$lista = [];

// 1. Obtenemos el ID del encabezado (re.id) para poder buscar sus detalles
$sql = "SELECT re.id as id_encabezado, re.id_encuesta, re.nombre_usuario, e.titulo 
        FROM respuestas_encabezado re 
        JOIN encuestas e ON re.id_encuesta = e.id 
        ORDER BY re.fecha_respuesta DESC";

$res = $con->query($sql);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $idEncabezado = $row['id_encabezado'];
        
        $respuestasArray = [];
        $sqlDetalle = "SELECT p.texto_pregunta, rd.puntuacion 
                       FROM respuestas_detalle rd
                       JOIN preguntas p ON rd.id_pregunta = p.id
                       WHERE rd.id_respuesta_encabezado = $idEncabezado";
        
        $resDetalle = $con->query($sqlDetalle);
        if ($resDetalle) {
            while ($d = $resDetalle->fetch_assoc()) {
                
                $respuestasArray[] = [
                    "texto_pregunta" => $d['texto_pregunta'], 
                    "puntuacion" => (int)$d['puntuacion']
                ];
            }
        }

        $item = [
            "id_encuesta" => (int)$row['id_encuesta'],
            "titulo" => $row['titulo'],
            "nombre_usuario" => $row['nombre_usuario'],
            "respuestas" => $respuestasArray 
        ];
        $lista[] = $item;
    }
}

echo json_encode($lista);
$conexionObj->close($con);
?>