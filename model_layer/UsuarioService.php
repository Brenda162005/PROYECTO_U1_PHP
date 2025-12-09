<?php
require_once "Conexion.php";

class UsuarioService {
    private Conexion $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    
    public function login($usuario, $password) {
            $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ? AND password = SHA2(?, 256)";
            $datosUsuario = null;

            try {
                $con = $this->conexion->open();
                $stmt = $con->prepare($sql);
                $stmt->bind_param("ss", $usuario, $password);
                $stmt->execute();
                
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    
                    // Convertir el 1 o 0 a true o false real
                    $row['es_admin'] = (bool)$row['es_admin']; 
                    // --------------------------------
                    
                    $datosUsuario = $row;
                }
                
                $stmt->close();
                $this->conexion->close($con);
            } catch (Exception $e) {
                return null;
            }
            return $datosUsuario;
        }

    // --- REGISTRO DE USUARIO 
    public function registrarUsuario($nombre, $password, $esAdmin, $archivoImagen) {
        
        $nombreFinalImagen = "no_image.jpg"; // Por defecto si no suben nada

        // Si se envió un archivo y no hay errores
        if (isset($archivoImagen) && $archivoImagen['error'] === UPLOAD_ERR_OK) {
            // Obteniene extensión (jpg, png)
            $ext = pathinfo($archivoImagen['name'], PATHINFO_EXTENSION);
           
            $nombreFinalImagen = $nombre . "_" . uniqid() . "." . $ext;
            
            
            $rutaDestino = "imagenes/" . $nombreFinalImagen;
            
            // mueve el archivo
            move_uploaded_file($archivoImagen['tmp_name'], $rutaDestino);
        }

        // Guardar en BD (Solo guarda el NOMBRE del archivo)
        $sql = "INSERT INTO usuarios (id, nombre_usuario, password, es_admin, imagen) VALUES (UUID(), ?, SHA2(?, 256), ?, ?)";
        $filas = 0;

        try {
            $con = $this->conexion->open();
            $stmt = $con->prepare($sql);
            // s=string, s=string, i=int, s=string
            $stmt->bind_param("ssis", $nombre, $password, $esAdmin, $nombreFinalImagen);
            
            $stmt->execute();
            $filas = $stmt->affected_rows;
            
            $stmt->close();
            $this->conexion->close($con);
        } catch (Exception $e) {
            
        }
        
        return $filas > 0;
    }
}
?>