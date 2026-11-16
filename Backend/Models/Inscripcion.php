<?php
class Inscripcion {
    // Cuenta en cuántos cursos activos está anotado el usuario
    public static function contarActivas($conexion, $usuario_id) {
        $sql = "SELECT COUNT(*) as total FROM inscripciones WHERE usuario_id = ? AND estado = 'activa'";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $fila = $resultado->fetch_assoc();
            $stmt->close();
            return $fila['total'];
        }
        return 0;
    }

    // Cuenta cuántos alumnos activos tiene un curso específico
    public static function contarPorCurso($conexion, $curso_id) {
        $sql = "SELECT COUNT(*) as total FROM inscripciones WHERE curso_id = ? AND estado = 'activa'";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $fila = $resultado->fetch_assoc();
            $stmt->close();
            return intval($fila['total']);
        }
        return 0;
    }
    
    // Registra la inscripción
    public static function registrar($conexion, $usuario_id, $curso_id) {
        try {
            $sql = "INSERT INTO inscripciones (usuario_id, curso_id) VALUES (?, ?)";
            $stmt = $conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ii", $usuario_id, $curso_id);
                $exito = $stmt->execute();
                $stmt->close();
                return $exito;
            }
            return false;
        } catch (mysqli_sql_exception $e) {
            // Si salta el error de restricción UNIQUE, lo capturamos y devolvemos false
            return false; 
        }
    }
}
?>