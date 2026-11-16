<?php
class Foro {
    
    // 1. Crear una nueva pregunta
    public static function crearPregunta($conexion, $curso_id, $usuario_id, $pregunta) {
        $sql = "INSERT INTO foro_preguntas (curso_id, usuario_id, pregunta) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iis", $curso_id, $usuario_id, $pregunta);
            $exito = $stmt->execute();
            $stmt->close();
            return $exito;
        }
        return false;
    }

    // 2. Crear una respuesta
    public static function crearRespuesta($conexion, $pregunta_id, $usuario_id, $respuesta) {
        $sql = "INSERT INTO foro_respuestas (pregunta_id, usuario_id, respuesta) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iis", $pregunta_id, $usuario_id, $respuesta);
            $exito = $stmt->execute();
            $stmt->close();
            return $exito;
        }
        return false;
    }

    // 3. Obtener todas las preguntas de un curso
    public static function obtenerPreguntasPorCurso($conexion, $curso_id) {
        $sql = "SELECT p.*, u.nombre_completo, u.imagen_perfil, u.plan 
                FROM foro_preguntas p 
                INNER JOIN usuarios u ON p.usuario_id = u.id 
                WHERE p.curso_id = ? 
                ORDER BY p.fecha DESC";
        $stmt = $conexion->prepare($sql);
        $preguntas = [];
        if ($stmt) {
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $preguntas[] = $fila;
            }
            $stmt->close();
        }
        return $preguntas;
    }

    // 4. Obtener las respuestas de una pregunta
    public static function obtenerRespuestasPorPregunta($conexion, $pregunta_id) {
        $sql = "SELECT r.*, u.nombre_completo, u.imagen_perfil, u.plan 
                FROM foro_respuestas r 
                INNER JOIN usuarios u ON r.usuario_id = u.id 
                WHERE r.pregunta_id = ? 
                ORDER BY r.es_aceptada DESC, r.fecha ASC"; // Las aceptadas salen primero
        $stmt = $conexion->prepare($sql);
        $respuestas = [];
        if ($stmt) {
            $stmt->bind_param("i", $pregunta_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $respuestas[] = $fila;
            }
            $stmt->close();
        }
        return $respuestas;
    }

    // 5. Marcar una respuesta como aceptada
    public static function aceptarRespuesta($conexion, $respuesta_id, $pregunta_id) {
        // Primero quitamos el "aceptada" a cualquier otra respuesta de esa pregunta
        $sql_reset = "UPDATE foro_respuestas SET es_aceptada = 0 WHERE pregunta_id = ?";
        $stmt_reset = $conexion->prepare($sql_reset);
        $stmt_reset->bind_param("i", $pregunta_id);
        $stmt_reset->execute();
        $stmt_reset->close();

        // Luego marcamos la elegida como aceptada
        $sql_accept = "UPDATE foro_respuestas SET es_aceptada = 1 WHERE id = ?";
        $stmt_accept = $conexion->prepare($sql_accept);
        $stmt_accept->bind_param("i", $respuesta_id);
        $exito = $stmt_accept->execute();
        $stmt_accept->close();
        
        return $exito;
    }
}
?>