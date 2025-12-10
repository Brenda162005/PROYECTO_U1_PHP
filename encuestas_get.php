<?php
require "model_layer/Conexion.php";
header("Content-Type: application/json; charset=utf-8");

$conexionObj = new Conexion();
$con = $conexionObj->open();
$con->set_charset("utf8mb4");

$lista = [];


$sql = "SELECT id, titulo, descripcion, esta_publicada, imagen FROM encuestas";

$res = $con->query($sql);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $item = [
            "id" => (int)$row['id'],
            "titulo" => $row['titulo'],
            "descripcion" => $row['descripcion'],
            "esta_publicada" => ($row['esta_publicada'] == 1), 
            
            "imagen" => $row['imagen']
        ];
        $lista[] = $item;
    }
}

echo json_encode($lista);
$conexionObj->close($con);
?>