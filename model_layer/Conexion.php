<?php
class Conexion {
    private string $host;
    private int $port;
    private string $user;
    private string $password;
    private string $database;

    public function __construct() {
        $this->host = "localhost";
        $this->port = 3307; 
        $this->user = "root";
        $this->password = "";
        $this->database = "proyecto_encuestas_u1"; 
    }

    public function open() {
        
        $conexion = new mysqli($this->host, $this->user, $this->password, $this->database, $this->port);
        
        if ($conexion->connect_error) {
            die("Error de conexión: " . $conexion->connect_error);
        }
        $conexion->set_charset("utf8mb4");
        return $conexion;
    }

    public function close($conexion) {
        $conexion->close();
    }
}
?>