<?php
require "model_layer/Conexion.php";
header("Content-Type: application/json; charset=utf-8");

// 1. Recibir el JSON crudo de Java
$json = file_get_contents("php://input");
$data = json_decode($json, true);

$response = array();

// Validamos que vengan los datos mínimos
if (isset($data['titulo']) && isset($data['nombre_usuario'])) {
    
    $conexionObj = new Conexion();
    $con = $conexionObj->open();
    $con->set_charset("utf8mb4");

   
    $con->begin_transaction();

    try {
       
        $titulo = $data['titulo'];
        $usuario = $data['nombre_usuario'];
        
        
        $stmt = $con->prepare("INSERT INTO encuestas (titulo, nombre_usuario, fecha_creacion) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $titulo, $usuario);
        $stmt->execute();
        
       
        $idEncuesta = $con->insert_id;
        $stmt->close();

       
        if (isset($data['preguntas']) && is_array($data['preguntas'])) {
            
           
            $sqlPregunta = "INSERT INTO preguntas (id_encuesta, texto_pregunta, id_pregunta_padre) VALUES (?, ?, ?)";
            $stmtPreg = $con->prepare($sqlPregunta);

            foreach ($data['preguntas'] as $preg) {
                $texto = $preg['texto_pregunta'];
                $padreId = null; 

                
                $stmtPreg->bind_param("isi", $idEncuesta, $texto, $padreId);
                $stmtPreg->execute();
                
            
                $idPreguntaInsertada = $con->insert_id;

                
                if (isset($preg['sub_preguntas']) && count($preg['sub_preguntas']) > 0) {
                    
                    foreach ($preg['sub_preguntas'] as $sub) {
                        $subTexto = $sub['texto_pregunta'];
                        
                        
                        $stmtPreg->bind_param("isi", $idEncuesta, $subTexto, $idPreguntaInsertada);
                        $stmtPreg->execute();
                    }
                }
            }
            $stmtPreg->close();
        }

        
        $con->commit();
        $response['success'] = "Encuesta publicada correctamente";

    } catch (Exception $e) {
        
        $con->rollback();
        $response['error'] = "Error al guardar en BD: " . $e->getMessage();
    }

    $conexionObj->close($con);

} else {
    $response['error'] = "Datos incompletos (Falta titulo o usuario)";
}

echo json_encode($response);
?>