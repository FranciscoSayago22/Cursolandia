<?php
class Usuario {
    // Atributos privados aplicando encapsulamiento
    private $id;
    private $nombreCompleto;
    private $email;
    private $password; // Debería ir hasheada (EN UN FUTURO)
    private $rol; // 'gratis' o 'pro'
    
    // Atributos dinámicos para llenar con __set
    private $imagen_perfil;
    private $titulo_profesional;
    private $comprobante_titulo;
    private $antecedentes;

    // Constructor para instanciar la clase
    public function __construct($nombreCompleto, $email, $password, $rol = 'gratis') {
        $this->nombreCompleto = $nombreCompleto;
        $this->email = $email;
        $this->password = $password; 
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
        $sql = "INSERT INTO usuarios (email, password, nombre_completo, imagen_perfil, titulo_profesional, comprobante_titulo, antecedentes, plan) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        
        if ($stmt) {
            // "ssssssss" indica que estamos pasando 8 variables de tipo String
            $stmt->bind_param("ssssssss", 
                $this->email, 
                $this->password, 
                $this->nombreCompleto, 
                $this->imagen_perfil, 
                $this->titulo_profesional, 
                $this->comprobante_titulo, 
                $this->antecedentes, 
                $this->rol
            );
            $resultado = $stmt->execute();
            $stmt->close();
            return $resultado;
        }
        return false;
    }

    // Método estático para verificar las credenciales en el login
    public static function autenticar($conexion, $email, $password) {
        $sql = "SELECT id, nombre_completo, password, imagen_perfil FROM usuarios WHERE email = ?";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            // Si el correo existe, verificamos la contraseña
            if ($fila = $resultado->fetch_assoc()) {
                if (password_verify($password, $fila['password'])) {
                    return $fila; // Retorna los datos del usuario si el login es correcto
                }
            }
            $stmt->close();
        }
        return false;
    }

    // Método para actualizar los datos personales
    public static function actualizarDatos($conexion, $id, $nombre, $titulo, $antecedentes, $ruta_imagen, $ruta_comprobante) {
        // Armamos la consulta base
        $sql = "UPDATE usuarios SET nombre_completo = ?, titulo_profesional = ?, antecedentes = ?";
        
        // Si subió una nueva imagen, la agregamos al UPDATE
        if ($ruta_imagen != null) {
            $sql .= ", imagen_perfil = '" . $conexion->real_escape_string($ruta_imagen) . "'";
        }
        
        // Si subió un nuevo comprobante, lo agregamos al UPDATE
        if ($ruta_comprobante != null) {
            $sql .= ", comprobante_titulo = '" . $conexion->real_escape_string($ruta_comprobante) . "'";
        }
        
        $sql .= " WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssi", $nombre, $titulo, $antecedentes, $id);
            $resultado = $stmt->execute();
            $stmt->close();
            return $resultado;
        }
        return false;
    }

    // Método para cambiar la contraseña validando la anterior
    public static function cambiarPassword($conexion, $id, $password_actual, $password_nueva) {
        // Primero buscamos la contraseña actual del usuario
        $sql = "SELECT password FROM usuarios WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($fila = $resultado->fetch_assoc()) {
                // Verificamos que la actual sea correcta
                if (password_verify($password_actual, $fila['password'])) {
                    
                    // Si es correcta, encriptamos la nueva y actualizamos
                    $password_hashed = password_hash($password_nueva, PASSWORD_DEFAULT);
                    $sql_update = "UPDATE usuarios SET password = ? WHERE id = ?";
                    $stmt_update = $conexion->prepare($sql_update);
                    
                    if ($stmt_update) {
                        $stmt_update->bind_param("si", $password_hashed, $id);
                        $exito = $stmt_update->execute();
                        $stmt_update->close();
                        return $exito;
                    }
                }
            }
            $stmt->close();
        }
        return false;
    }

    // Método para obtener todos los datos de un usuario por su ID
    public static function obtenerPorId($conexion, $id) {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();
            $stmt->close();
            
            return $usuario;
        }
        return false;
    }
}
?>