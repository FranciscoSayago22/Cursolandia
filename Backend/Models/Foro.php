<?php
require_once __DIR__ . '/Notificacion.php';

class Foro {

    private static function obtenerNombreUsuario($conexion, $usuario_id) {
        $sql = "SELECT nombre_completo FROM usuarios WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $fila = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $fila ? $fila['nombre_completo'] : 'Un usuario';
        }
        return 'Un usuario';
    }

    // 1. Crear pregunta y notificar a TODOS los participantes del curso (educador + inscriptos)
    public static function crearPregunta($conexion, $curso_id, $usuario_id, $pregunta) {
        $sql = "INSERT INTO foro_preguntas (curso_id, usuario_id, pregunta) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("iis", $curso_id, $usuario_id, $pregunta);
        $exito = $stmt->execute();
        $stmt->close();

        if ($exito) {
            $sql_c = "SELECT titulo, creador_id FROM cursos WHERE id = ?";
            $stmt_c = $conexion->prepare($sql_c);
            $stmt_c->bind_param("i", $curso_id);
            $stmt_c->execute();
            $curso = $stmt_c->get_result()->fetch_assoc();
            $stmt_c->close();

            if ($curso) {
                $autor = self::obtenerNombreUsuario($conexion, $usuario_id);
                $destinatarios = [$curso['creador_id']];

                $sql_ins = "SELECT usuario_id FROM inscripciones WHERE curso_id = ? AND estado = 'activa'";
                $stmt_ins = $conexion->prepare($sql_ins);
                $stmt_ins->bind_param("i", $curso_id);
                $stmt_ins->execute();
                $res_ins = $stmt_ins->get_result();
                while ($row = $res_ins->fetch_assoc()) {
                    $destinatarios[] = intval($row['usuario_id']);
                }
                $stmt_ins->close();

                $destinatarios = array_diff(array_unique($destinatarios), [$usuario_id]);
                $mensaje = "$autor realizó una nueva pregunta en el curso \"{$curso['titulo']}\".";
                $enlace = "curso.php?id=" . $curso_id . "#foro";

                foreach ($destinatarios as $dest_id) {
                    Notificacion::crear($conexion, $dest_id, 'nueva_pregunta', $mensaje, $enlace);
                }
            }
        }
        return $exito;
    }

    // 2. Crear respuesta (validando que no sea cerrada ni 2 seguidas) y notificar participantes
    public static function crearRespuesta($conexion, $pregunta_id, $usuario_id, $respuesta) {
        // Verificar que la pregunta exista y no esté cerrada
        $sql_p = "SELECT p.usuario_id AS autor_pregunta_id, p.cerrada, p.curso_id, c.titulo AS curso_titulo, c.creador_id AS educador_id
                  FROM foro_preguntas p
                  INNER JOIN cursos c ON p.curso_id = c.id
                  WHERE p.id = ?";
        $stmt_p = $conexion->prepare($sql_p);
        $stmt_p->bind_param("i", $pregunta_id);
        $stmt_p->execute();
        $info_p = $stmt_p->get_result()->fetch_assoc();
        $stmt_p->close();

        if (!$info_p || intval($info_p['cerrada']) === 1) {
            return 'cerrada';
        }

        // Regla: Un usuario no puede responder 2 veces seguidas la misma pregunta
        $sql_last = "SELECT usuario_id FROM foro_respuestas WHERE pregunta_id = ? ORDER BY id DESC LIMIT 1";
        $stmt_last = $conexion->prepare($sql_last);
        $stmt_last->bind_param("i", $pregunta_id);
        $stmt_last->execute();
        $ultima = $stmt_last->get_result()->fetch_assoc();
        $stmt_last->close();

        if ($ultima && intval($ultima['usuario_id']) === intval($usuario_id)) {
            return 'respuesta_seguida';
        }

        // Insertar la respuesta
        $sql = "INSERT INTO foro_respuestas (pregunta_id, usuario_id, respuesta) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("iis", $pregunta_id, $usuario_id, $respuesta);
        $exito = $stmt->execute();
        $stmt->close();

        if ($exito) {
            $autor = self::obtenerNombreUsuario($conexion, $usuario_id);
            $destinatarios = [
                intval($info_p['autor_pregunta_id']),
                intval($info_p['educador_id'])
            ];

            $sql_resp = "SELECT DISTINCT usuario_id FROM foro_respuestas WHERE pregunta_id = ?";
            $stmt_resp = $conexion->prepare($sql_resp);
            $stmt_resp->bind_param("i", $pregunta_id);
            $stmt_resp->execute();
            $res_resp = $stmt_resp->get_result();
            while ($r = $res_resp->fetch_assoc()) {
                $destinatarios[] = intval($r['usuario_id']);
            }
            $stmt_resp->close();

            $destinatarios = array_diff(array_unique($destinatarios), [$usuario_id]);
            $mensaje = "$autor respondió en una pregunta del curso \"{$info_p['curso_titulo']}\".";
            $enlace = "curso.php?id=" . $info_p['curso_id'] . "#foro";

            foreach ($destinatarios as $dest_id) {
                Notificacion::crear($conexion, $dest_id, 'nueva_respuesta', $mensaje, $enlace);
            }
            return true;
        }
        return false;
    }

    // 2b. Editar la última respuesta de la pregunta (si pertenece al usuario y la pregunta sigue abierta)
    public static function editarRespuesta($conexion, $respuesta_id, $pregunta_id, $usuario_id, $nuevo_texto) {
        $sql_p = "SELECT cerrada FROM foro_preguntas WHERE id = ?";
        $stmt_p = $conexion->prepare($sql_p);
        $stmt_p->bind_param("i", $pregunta_id);
        $stmt_p->execute();
        $preg = $stmt_p->get_result()->fetch_assoc();
        $stmt_p->close();

        if (!$preg || intval($preg['cerrada']) === 1) {
            return false;
        }

        // Confirmamos que sea efectivamente la última respuesta de la pregunta y del usuario
        $sql_last = "SELECT id, usuario_id FROM foro_respuestas WHERE pregunta_id = ? ORDER BY id DESC LIMIT 1";
        $stmt_last = $conexion->prepare($sql_last);
        $stmt_last->bind_param("i", $pregunta_id);
        $stmt_last->execute();
        $ultima = $stmt_last->get_result()->fetch_assoc();
        $stmt_last->close();

        if (!$ultima || intval($ultima['id']) !== intval($respuesta_id) || intval($ultima['usuario_id']) !== intval($usuario_id)) {
            return false;
        }

        $sql = "UPDATE foro_respuestas SET respuesta = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $nuevo_texto, $respuesta_id);
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
                ORDER BY p.fecha DESC, p.id DESC";
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
                ORDER BY r.es_aceptada DESC, r.fecha ASC, r.id ASC";
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

    // 5. Destacar una respuesta como útil
    public static function aceptarRespuesta($conexion, $respuesta_id, $pregunta_id, $usuario_id) {
        $sql_permiso = "SELECT p.id FROM foro_preguntas p
                        INNER JOIN cursos c ON p.curso_id = c.id
                        WHERE p.id = ? AND (p.usuario_id = ? OR c.creador_id = ?)";
        $stmt_perm = $conexion->prepare($sql_permiso);
        $stmt_perm->bind_param("iii", $pregunta_id, $usuario_id, $usuario_id);
        $stmt_perm->execute();
        $tiene_permiso = ($stmt_perm->get_result()->num_rows > 0);
        $stmt_perm->close();

        if (!$tiene_permiso) return false;

        $sql_reset = "UPDATE foro_respuestas SET es_aceptada = 0 WHERE pregunta_id = ?";
        $stmt_reset = $conexion->prepare($sql_reset);
        $stmt_reset->bind_param("i", $pregunta_id);
        $stmt_reset->execute();
        $stmt_reset->close();

        $sql_accept = "UPDATE foro_respuestas SET es_aceptada = 1 WHERE id = ? AND pregunta_id = ?";
        $stmt_accept = $conexion->prepare($sql_accept);
        $stmt_accept->bind_param("ii", $respuesta_id, $pregunta_id);
        $exito = $stmt_accept->execute();
        $stmt_accept->close();

        return $exito;
    }

    // 6. Cerrar una pregunta y notificar a los participantes (con mención especial a la respuesta destacada)
    public static function cerrarPregunta($conexion, $pregunta_id, $usuario_id) {
        $sql_info = "SELECT p.usuario_id AS autor_pregunta_id, p.cerrada, p.curso_id, c.titulo AS curso_titulo, c.creador_id AS educador_id
                     FROM foro_preguntas p
                     INNER JOIN cursos c ON p.curso_id = c.id
                     WHERE p.id = ?";
        $stmt_info = $conexion->prepare($sql_info);
        $stmt_info->bind_param("i", $pregunta_id);
        $stmt_info->execute();
        $info = $stmt_info->get_result()->fetch_assoc();
        $stmt_info->close();

        if (!$info || intval($info['cerrada']) === 1) return false;
        if (intval($usuario_id) !== intval($info['autor_pregunta_id']) && intval($usuario_id) !== intval($info['educador_id'])) {
            return false;
        }

        $sql_close = "UPDATE foro_preguntas SET cerrada = 1 WHERE id = ?";
        $stmt_close = $conexion->prepare($sql_close);
        $stmt_close->bind_param("i", $pregunta_id);
        $exito = $stmt_close->execute();
        $stmt_close->close();

        if ($exito) {
            $enlace = "curso.php?id=" . $info['curso_id'] . "#foro";

            // Buscar si hay respuesta destacada
            $sql_dest = "SELECT usuario_id FROM foro_respuestas WHERE pregunta_id = ? AND es_aceptada = 1 LIMIT 1";
            $stmt_dest = $conexion->prepare($sql_dest);
            $stmt_dest->bind_param("i", $pregunta_id);
            $stmt_dest->execute();
            $destacada = $stmt_dest->get_result()->fetch_assoc();
            $stmt_dest->close();

            $usuario_destacado_id = $destacada ? intval($destacada['usuario_id']) : null;

            // Notificación especial al autor de la respuesta destacada
            if ($usuario_destacado_id && $usuario_destacado_id !== intval($usuario_id)) {
                $msg_especial = "¡Tu respuesta fue destacada como solución al cerrarse una pregunta en \"{$info['curso_titulo']}\"!";
                Notificacion::crear($conexion, $usuario_destacado_id, 'respuesta_destacada', $msg_especial, $enlace);
            }

            // Notificar al resto de participantes de la pregunta
            $participantes = [
                intval($info['autor_pregunta_id']),
                intval($info['educador_id'])
            ];

            $sql_part = "SELECT DISTINCT usuario_id FROM foro_respuestas WHERE pregunta_id = ?";
            $stmt_part = $conexion->prepare($sql_part);
            $stmt_part->bind_param("i", $pregunta_id);
            $stmt_part->execute();
            $res_part = $stmt_part->get_result();
            while ($row = $res_part->fetch_assoc()) {
                $participantes[] = intval($row['usuario_id']);
            }
            $stmt_part->close();

            $excluir = [intval($usuario_id)];
            if ($usuario_destacado_id) {
                $excluir[] = $usuario_destacado_id;
            }
            $participantes = array_diff(array_unique($participantes), $excluir);

            $msg_general = "Se cerró una pregunta en la que participaste en el curso \"{$info['curso_titulo']}\".";
            foreach ($participantes as $part_id) {
                Notificacion::crear($conexion, $part_id, 'pregunta_cerrada', $msg_general, $enlace);
            }
        }
        return $exito;
    }

    // 7. Eliminar una pregunta y notificar a su creador
    public static function eliminarPregunta($conexion, $pregunta_id, $usuario_id, $es_admin = false) {
        $sql_info = "SELECT p.usuario_id AS autor_pregunta_id, p.curso_id, c.titulo AS curso_titulo, c.creador_id AS educador_id
                     FROM foro_preguntas p
                     INNER JOIN cursos c ON p.curso_id = c.id
                     WHERE p.id = ?";
        $stmt_info = $conexion->prepare($sql_info);
        $stmt_info->bind_param("i", $pregunta_id);
        $stmt_info->execute();
        $info = $stmt_info->get_result()->fetch_assoc();
        $stmt_info->close();

        if (!$info) return false;

        $autor_id = intval($info['autor_pregunta_id']);
        $educador_id = intval($info['educador_id']);

        if (!$es_admin && intval($usuario_id) !== $autor_id && intval($usuario_id) !== $educador_id) {
            return false;
        }

        $sql_del = "DELETE FROM foro_preguntas WHERE id = ?";
        $stmt_del = $conexion->prepare($sql_del);
        $stmt_del->bind_param("i", $pregunta_id);
        $exito = $stmt_del->execute() && $stmt_del->affected_rows > 0;
        $stmt_del->close();

        // Notificar al creador de la pregunta si fue eliminada por el educador o admin
        if ($exito && $autor_id !== intval($usuario_id)) {
            $mensaje = "Tu pregunta en el curso \"{$info['curso_titulo']}\" fue eliminada.";
            $enlace = "curso.php?id=" . $info['curso_id'] . "#foro";
            Notificacion::crear($conexion, $autor_id, 'pregunta_eliminada', $mensaje, $enlace);
        }

        return $exito;
    }
}
?>