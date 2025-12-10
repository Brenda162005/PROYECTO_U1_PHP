<?php
require "model_layer/Conexion.php";
header("Content-Type: application/json; charset=utf-8");

$conexionObj = new Conexion();
$con = $conexionObj->open();
$con->set_charset("utf8mb4");

$lista = [];

// --- CORRECCIÓN SQL: Agregamos explícitamente 're.id_encuesta' ---
$sql = "SELECT re.id_encuesta, re.nombre_usuario, e.titulo 
        FROM respuestas_encabezado re 
        JOIN encuestas e ON re.id_encuesta = e.id 
        ORDER BY re.fecha_respuesta DESC";

$res = $con->query($sql);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $item = [
            // --- ESTE ES EL DATO CRUCIAL PARA QUE JAVA FILTRE ---
            // Usamos la clave "id_encuesta" para que coincida con el @SerializedName de Java
            "id_encuesta" => (int)$row['id_encuesta'], 
            
            // Usamos "titulo" y "nombre_usuario" para coincidir con el modelo Java actualizado
            "titulo" => $row['titulo'],
            "nombre_usuario" => $row['nombre_usuario'],
            
            "respuestas" => [] // Campo vacío necesario para el constructor de Java
        ];
        $lista[] = $item;
    }
}

echo json_encode($lista);
$conexionObj->close($con);
?>