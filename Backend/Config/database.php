<?php
class Conexion {
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $db_name = "cursolandia";
    private $conn;

    public function conectar() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->db_name);

        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
        
        // Configuramos el charset para evitar problemas con tildes y ñ
        $this->conn->set_charset("utf8");
        
        return $this->conn;
    }
}
?>