<?php
require "model_layer/Conexion.php";
header("Content-Type: application/json; charset=utf-8");

$conexionObj = new Conexion();
$con = $conexionObj->open();
$con->set_charset("utf8mb4");

$titulo = $_POST['titulo'] ?? '';
$resultados = [];

if (!empty($titulo)) {
    // 1. Obtener ID de la encuesta
    $stmt = $con->prepare("SELECT id FROM encuestas WHERE titulo = ?");
    $stmt->bind_param("s", $titulo);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $idEncuesta = $row['id'];
        
        // 2. Inicializar el mapa con ceros para todas las preguntas
        $stmtP = $con->prepare("SELECT texto_pregunta FROM preguntas WHERE id_encuesta = ?");
        $stmtP->bind_param("i", $idEncuesta);
        $stmtP->execute();
        $resP = $stmtP->get_result();
        while ($rowP = $resP->fetch_assoc()) {
            // [1, 2, 3, 4, 5] -> Inicializamos en 0 votos
            $resultados[$rowP['texto_pregunta']] = [0, 0, 0, 0, 0];
        }
        $stmtP->close();

        // 3. Contar los votos reales
        $sqlVotos = "SELECT p.texto_pregunta, rd.puntuacion 
                     FROM respuestas_detalle rd 
                     JOIN preguntas p ON rd.id_pregunta = p.id 
                     JOIN respuestas_encabezado re ON rd.id_respuesta_encabezado = re.id 
                     WHERE re.id_encuesta = ?";
        
        $stmtV = $con->prepare($sqlVotos);
        $stmtV->bind_param("i", $idEncuesta);
        $stmtV->execute();
        $resV = $stmtV->get_result();

        while ($rowV = $resV->fetch_assoc()) {
            $pregunta = $rowV['texto_pregunta'];
            $puntos = (int)$rowV['puntuacion'];

            // Sumar el voto en la posición correcta (puntos 1 a 5)
            if (isset($resultados[$pregunta]) && $puntos >= 1 && $puntos <= 5) {
                $resultados[$pregunta][$puntos - 1]++;
            }
        }
        $stmtV->close();
    }
}

echo json_encode($resultados);
$conexionObj->close($con);
?>