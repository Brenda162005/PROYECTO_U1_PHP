<?php
require "model_layer/Conexion.php";
header("Content-Type: application/json; charset=utf-8");

$conexionObj = new Conexion();
$con = $conexionObj->open();
$con->set_charset("utf8mb4");

// Obtenemos el ID de la encuesta que nos manda Java
$idEncuesta = isset($_GET['id_encuesta']) ? $_GET['id_encuesta'] : null;
$listaFinal = [];

if ($idEncuesta) {
    // 1. Traemos TODAS las preguntas de esa encuesta
    $sql = "SELECT id, id_encuesta, texto_pregunta, id_pregunta_padre 
            FROM preguntas 
            WHERE id_encuesta = ?";
    
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $idEncuesta);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $todasLasPreguntas = [];

    // 2. Las guardamos en un arreglo temporal
    while ($row = $result->fetch_assoc()) {
        $p = [
            "id" => (int)$row['id'],
            "id_encuesta" => (int)$row['id_encuesta'],
            "texto_pregunta" => $row['texto_pregunta'], // Coincide con tu @SerializedName
            "id_pregunta_padre" => $row['id_pregunta_padre'] ? (int)$row['id_pregunta_padre'] : null,
            "sub_preguntas" => [] // Preparamos el nido para las hijas
        ];
        $todasLasPreguntas[$p['id']] = $p;
    }

    // 3. Armamos el árbol (Padres e Hijos)
    // Esto es vital para que tu estructura de Java funcione
    foreach ($todasLasPreguntas as $id => &$pregunta) {
        if ($pregunta['id_pregunta_padre'] != null) {
            // Es hija: La metemos dentro de su padre
            $idPadre = $pregunta['id_pregunta_padre'];
            if (isset($todasLasPreguntas[$idPadre])) {
                $todasLasPreguntas[$idPadre]['sub_preguntas'][] = &$pregunta;
            }
        }
    }

    // 4. Filtramos solo las preguntas Raíz (las que no tienen padre) para enviar
    foreach ($todasLasPreguntas as $id => $pregunta) {
        if ($pregunta['id_pregunta_padre'] == null) {
            $listaFinal[] = $pregunta;
        }
    }
}

echo json_encode($listaFinal);
$conexionObj->close($con);
?>