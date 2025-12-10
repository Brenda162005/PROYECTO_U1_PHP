<?php
require "model_layer/Conexion.php";
header("Content-Type: application/json; charset=utf-8");

$conexionObj = new Conexion();
$con = $conexionObj->open();
$con->set_charset("utf8mb4");

$jsonRecibido = file_get_contents('php://input');
$data = json_decode($jsonRecibido, true);

if ($data) {
   
    $idEncuesta = isset($data['id_encuesta']) ? (int)$data['id_encuesta'] : 0;
    
    $nombreUsuario = $data['nombre_usuario'] ?? $data['nombreUsuario'] ?? 'Anónimo';
    
    $respuestas = $data['respuestas'] ?? [];
    if ($idEncuesta <= 0) {
        $titulo = $data['tituloEncuesta'] ?? '';
        $stmtB = $con->prepare("SELECT id FROM encuestas WHERE titulo = ?");
        $stmtB->bind_param("s", $titulo);
        $stmtB->execute();
        $resB = $stmtB->get_result();
        if ($row = $resB->fetch_assoc()) {
            $idEncuesta = $row['id'];
        }
        $stmtB->close();
    }

    if ($idEncuesta > 0) {
        $con->begin_transaction();
        try {
           
            $stmtHead = $con->prepare("INSERT INTO respuestas_encabezado (id_encuesta, nombre_usuario, fecha_respuesta) VALUES (?, ?, NOW())");
            $stmtHead->bind_param("is", $idEncuesta, $nombreUsuario);
            $stmtHead->execute();
            $idEncabezado = $con->insert_id;
            $stmtHead->close();

            
            $stmtDetalle = $con->prepare("INSERT INTO respuestas_detalle (id_respuesta_encabezado, id_pregunta, puntuacion) VALUES (?, ?, ?)");
            $stmtBuscaP = $con->prepare("SELECT id FROM preguntas WHERE texto_pregunta = ? AND id_encuesta = ?");

            foreach ($respuestas as $r) {
                $texto = $r['textoPregunta'];
                $puntos = $r['puntuacion'];

                
                $stmtBuscaP->bind_param("si", $texto, $idEncuesta);
                $stmtBuscaP->execute();
                $resP = $stmtBuscaP->get_result();

                if ($rowP = $resP->fetch_assoc()) {
                    $idPregunta = $rowP['id'];
                    $stmtDetalle->bind_param("iii", $idEncabezado, $idPregunta, $puntos);
                    $stmtDetalle->execute();
                }
            }
            $stmtBuscaP->close();
            $stmtDetalle->close();

            $con->commit();
            echo json_encode(["success" => "Respuestas guardadas"]);
            
        } catch (Exception $e) {
            $con->rollback();
            echo json_encode(["error" => "Error SQL: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Encuesta no encontrada"]);
    }
} else {
    echo json_encode(["error" => "Sin datos"]);
}
$conexionObj->close($con);
?>