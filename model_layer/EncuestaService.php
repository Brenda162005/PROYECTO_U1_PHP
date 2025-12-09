<?php
require_once "Conexion.php";

class EncuestaService {
    private Conexion $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    
    public function getEncuestas() {
        $sql = "SELECT * FROM encuestas";
        $lista = [];

        try {
            $con = $this->conexion->open();
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                
                $row['esta_publicada'] = (bool)$row['esta_publicada'];
                $lista[] = $row;
            }
            
            $stmt->close();
            $this->conexion->close($con);
        } catch (Exception $e) {
            return [];
        }
        return $lista;
    }

    //  CREAR ENCUESTA COMPLETA 
    public function crearEncuestaCompleta($titulo, $descripcion, $publicada, $imagenArchivo, $preguntasJson) {
        $con = $this->conexion->open();
        
        
        $con->begin_transaction();

        try {
           
            $nombreImagen = "no_image.jpg"; // Imagen por defecto
            
            if (isset($imagenArchivo) && $imagenArchivo['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($imagenArchivo['name'], PATHINFO_EXTENSION);
                // Nombre único para evitar sobrescribir
                $nombreImagen = "encuesta_" . uniqid() . "." . $ext;
                
                
                move_uploaded_file($imagenArchivo['tmp_name'], "imagenes/" . $nombreImagen);
            }

            
            $sqlEncuesta = "INSERT INTO encuestas (titulo, descripcion, esta_publicada, imagen) VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($sqlEncuesta);
            $pubInt = $publicada ? 1 : 0; 
            $stmt->bind_param("ssis", $titulo, $descripcion, $pubInt, $nombreImagen);
            $stmt->execute();
            
           
            $idEncuesta = $con->insert_id;
            $stmt->close();

            //  Insertar Preguntas 
            $preguntasArray = json_decode($preguntasJson, true); 
            
            if ($preguntasArray && count($preguntasArray) > 0) {
                
                $this->guardarPreguntasRecursivo($con, $idEncuesta, $preguntasArray, null);
            }

            
            $con->commit();
            $this->conexion->close($con);
            return true;

        } catch (Exception $e) {
           
            $con->rollback();
            $this->conexion->close($con);
            return false;
        }
    }

  
    private function guardarPreguntasRecursivo($con, $idEncuesta, $preguntas, $idPadre) {
        $sql = "INSERT INTO preguntas (id_encuesta, texto_pregunta, id_pregunta_padre) VALUES (?, ?, ?)";
        $stmt = $con->prepare($sql);

        foreach ($preguntas as $p) {
            $texto = $p['texto_pregunta']; 
            
            $stmt->bind_param("isi", $idEncuesta, $texto, $idPadre);
            $stmt->execute();
            
            
            $idPreguntaNueva = $con->insert_id;

           
            if (isset($p['sub_preguntas']) && count($p['sub_preguntas']) > 0) {
                $this->guardarPreguntasRecursivo($con, $idEncuesta, $p['sub_preguntas'], $idPreguntaNueva);
            }
        }
        $stmt->close();
    }

    // BORRAR ENCUESTA (Por Título) 
    public function borrarEncuesta($titulo) {
        $con = $this->conexion->open();
        $filasAfectadas = 0;

        try {
            // 1. Obtener ID de la encuesta
            $sqlId = "SELECT id FROM encuestas WHERE titulo = ?";
            $stmtId = $con->prepare($sqlId);
            $stmtId->bind_param("s", $titulo);
            $stmtId->execute();
            $result = $stmtId->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $idEncuesta = $row['id'];
                
              
                $con->begin_transaction();

                // Borrar respuestas primero (Para evitar error de llaves foráneas)
                $sqlDelRespuestas = "DELETE FROM respuestas WHERE id_pregunta IN (SELECT id FROM preguntas WHERE id_encuesta = ?)";
                $stmtR = $con->prepare($sqlDelRespuestas);
                $stmtR->bind_param("i", $idEncuesta);
                $stmtR->execute();
                $stmtR->close();

                // B. Borrar preguntas asociadas
                $sqlDelPreguntas = "DELETE FROM preguntas WHERE id_encuesta = ?";
                $stmtP = $con->prepare($sqlDelPreguntas);
                $stmtP->bind_param("i", $idEncuesta);
                $stmtP->execute();
                $stmtP->close();

                // C. Borrar la encuesta
                $sqlDelEncuesta = "DELETE FROM encuestas WHERE id = ?";
                $stmtE = $con->prepare($sqlDelEncuesta);
                $stmtE->bind_param("i", $idEncuesta);
                $stmtE->execute();
                
                $filasAfectadas = $stmtE->affected_rows;
                $stmtE->close();

                $con->commit();
            }
            
            $stmtId->close();
            $this->conexion->close($con);

        } catch (Exception $e) {
            $con->rollback();
            return false;
        }

        return $filasAfectadas > 0;
    }

    // PUBLICAR ENCUESTA (EL MÉTODO QUE FALTABA) ---
    public function publicarEncuesta($titulo) {
        $con = $this->conexion->open();
        $filasAfectadas = 0;

        try {
            $sql = "UPDATE encuestas SET esta_publicada = 1 WHERE titulo = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("s", $titulo);
            $stmt->execute();
            
            $filasAfectadas = $stmt->affected_rows;
            
            $stmt->close();
            $this->conexion->close($con);
        } catch (Exception $e) {
            return false;
        }
        return $filasAfectadas > 0;
    }
}
?>