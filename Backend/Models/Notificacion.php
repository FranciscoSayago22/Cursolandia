<?php
class Notificacion {

    // Crear una notificación para un usuario
    public static function crear($conexion, $usuario_id, $tipo, $mensaje, $enlace) {
        $sql = "INSERT INTO notificaciones (usuario_id, tipo, mensaje, enlace) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isss", $usuario_id, $tipo, $mensaje, $enlace);
            $exito = $stmt->execute();
            $stmt->close();
            return $exito;
        }
        return false;
    }

    // Contar notificaciones sin leer de un usuario
    public static function contarNoLeidas($conexion, $usuario_id) {
        $sql = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ? AND leida = 0";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $fila = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return intval($fila['total']);
        }
        return 0;
    }

    // Obtener las últimas notificaciones de un usuario
    public static function obtenerPorUsuario($conexion, $usuario_id, $limite = 15) {
        $sql = "SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY leida ASC, fecha DESC, id DESC LIMIT ?";
        $stmt = $conexion->prepare($sql);
        $lista = [];
        if ($stmt) {
            $stmt->bind_param("ii", $usuario_id, $limite);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($fila = $res->fetch_assoc()) {
                $lista[] = $fila;
            }
            $stmt->close();
        }
        return $lista;
    }

    // Obtener una notificación puntual (verificando dueño)
    public static function obtenerPorId($conexion, $id, $usuario_id) {
        $sql = "SELECT * FROM notificaciones WHERE id = ? AND usuario_id = ?";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $id, $usuario_id);
            $stmt->execute();
            $notif = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $notif;
        }
        return null;
    }

    // Marcar una notificación como leída
    public static function marcarLeida($conexion, $id, $usuario_id) {
        $sql = "UPDATE notificaciones SET leida = 1 WHERE id = ? AND usuario_id = ?";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $id, $usuario_id);
            $exito = $stmt->execute();
            $stmt->close();
            return $exito;
        }
        return false;
    }

    // Marcar todas como leídas
    public static function marcarTodasLeidas($conexion, $usuario_id) {
        $sql = "UPDATE notificaciones SET leida = 1 WHERE usuario_id = ? AND leida = 0";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $exito = $stmt->execute();
            $stmt->close();
            return $exito;
        }
        return false;
    }

    // Eliminar una notificación
    public static function eliminar($conexion, $id, $usuario_id) {
        $sql = "DELETE FROM notificaciones WHERE id = ? AND usuario_id = ?";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $id, $usuario_id);
            $exito = $stmt->execute();
            $stmt->close();
            return $exito;
        }
        return false;
    }

    // Eliminar todas las notificaciones ya leídas
    public static function eliminarLeidas($conexion, $usuario_id) {
        $sql = "DELETE FROM notificaciones WHERE usuario_id = ? AND leida = 1";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $exito = $stmt->execute();
            $stmt->close();
            return $exito;
        }
        return false;
    }
}
?>