<?php
class Usuario {
    // Atributos privados aplicando encapsulamiento
    private $id;
    private $nombreCompleto;
    private $email;
    private $password;
    private $rol; // 'gratis' o 'pro'

    // Constructor para instanciar la clase
    public function __construct($nombreCompleto, $email, $password, $rol = 'gratis') {
        $this->nombreCompleto = $nombreCompleto;
        $this->email = $email;
        $this->password = $password; // Debería ir hasheada (EN UN FUTURO)
        $this->rol = $rol;
    }

    // Métodos __get y __set para manipular atributos
    public function __get($propiedad) {
        if (property_exists($this, $propiedad)) {
            return $this->$propiedad;
        }
    }

    public function __set($propiedad, $valor) {
        if (property_exists($this, $propiedad)) {
            $this->$propiedad = $valor;
        }
    }

    // Método para guardar el usuario en la BD 
    public function guardar($conexion) {
        // Preparamos la consulta para evitar inyecciones SQL
        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ssss", $this->nombreCompleto, $this->email, $this->password, $this->rol);
            $resultado = $stmt->execute();
            $stmt->close();
            return $resultado;
        }
        return false;
    }
}
?>