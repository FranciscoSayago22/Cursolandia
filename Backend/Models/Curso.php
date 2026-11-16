<?php
class Curso {
    
    // Método estático transaccional para crear todo el ecosistema del curso
    public static function crear($conexion, $creador_id, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $estado, $etiquetas, $archivos_validados) {
        try {
            // Iniciamos la transacción: Si una consulta falla, NADA se guarda en la base de datos
            $conexion->begin_transaction();

            // 1. Insertar el Curso
            $sql_curso = "INSERT INTO cursos (creador_id, titulo, descripcion, costo, cupo, fecha_inicio, fecha_fin, estado) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql_curso);
            
            // Si el cupo viene vacío (sin límite), lo pasamos como NULL a la BD
            $cupo_bind = ($cupo === '' || $cupo === null) ? null : intval($cupo);
            
            $stmt->bind_param("issdisss", $creador_id, $titulo, $descripcion, $costo, $cupo_bind, $fecha_inicio, $fecha_fin, $estado);
            $stmt->execute();
            
            // Capturamos el ID del curso recién creado para usarlo en las otras tablas
            $curso_id = $conexion->insert_id;
            $stmt->close();

            // 2. Procesar e Insertar Etiquetas (Relación N:M)
            if (!empty($etiquetas)) {
                // Separamos por coma y limpiamos espacios
                $array_etiquetas = array_map('trim', explode(',', $etiquetas));
                
                foreach ($array_etiquetas as $nombre_etiqueta) {
                    if (empty($nombre_etiqueta)) continue;

                    $etiqueta_id = null;
                    
                    // ¿Existe la etiqueta?
                    $sql_check = "SELECT id FROM etiquetas WHERE nombre = ?";
                    $stmt_check = $conexion->prepare($sql_check);
                    $stmt_check->bind_param("s", $nombre_etiqueta);
                    $stmt_check->execute();
                    $resultado = $stmt_check->get_result();
                    
                    if ($fila = $resultado->fetch_assoc()) {
                        $etiqueta_id = $fila['id']; // Ya existe
                    } else {
                        // No existe, la creamos
                        $sql_insert_tag = "INSERT INTO etiquetas (nombre) VALUES (?)";
                        $stmt_tag = $conexion->prepare($sql_insert_tag);
                        $stmt_tag->bind_param("s", $nombre_etiqueta);
                        $stmt_tag->execute();
                        $etiqueta_id = $conexion->insert_id;
                        $stmt_tag->close();
                    }
                    $stmt_check->close();

                    // Vinculamos la etiqueta al curso en la tabla intermedia
                    $sql_pivot = "INSERT INTO curso_etiquetas (curso_id, etiqueta_id) VALUES (?, ?)";
                    $stmt_pivot = $conexion->prepare($sql_pivot);
                    $stmt_pivot->bind_param("ii", $curso_id, $etiqueta_id);
                    $stmt_pivot->execute();
                    $stmt_pivot->close();
                }
            }

            // 3. Insertar Material Adjunto
            if (!empty($archivos_validados)) {
                $sql_material = "INSERT INTO material_curso (curso_id, nombre_archivo, peso_bytes) VALUES (?, ?, ?)";
                $stmt_mat = $conexion->prepare($sql_material);
                
                foreach ($archivos_validados as $archivo) {
                    $stmt_mat->bind_param("isi", $curso_id, $archivo['nombre'], $archivo['peso']);
                    $stmt_mat->execute();
                }
                $stmt_mat->close();
            }

            // Si todo salió perfecto, confirmamos la transacción
            $conexion->commit();
            return true;

        } catch (Exception $e) {
            // Si hubo cualquier error, deshacemos todos los INSERTs de este proceso
            $conexion->rollback();
            return false;
        }
    }

   // Obtiene los cursos creados por usuarios PRO o ADMIN (Destacados), con filtro opcional
    public static function obtenerDestacados($conexion, $busqueda = '') {
        if ($busqueda !== '') {
            $termino = "%" . $busqueda . "%";
            $sql = "SELECT DISTINCT c.*, u.nombre_completo as creador_nombre, u.imagen_perfil as creador_imagen, u.plan 
                    FROM cursos c 
                    INNER JOIN usuarios u ON c.creador_id = u.id 
                    LEFT JOIN curso_etiquetas ce ON c.id = ce.curso_id 
                    LEFT JOIN etiquetas e ON ce.etiqueta_id = e.id 
                    WHERE (u.plan = 'pro' OR u.plan = 'admin') 
                      AND c.estado = 'publico' 
                      AND (c.titulo LIKE ? OR e.nombre LIKE ?) 
                    ORDER BY c.fecha_creacion DESC LIMIT 3";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ss", $termino, $termino);
            $stmt->execute();
            return $stmt->get_result();
        }

        $sql = "SELECT c.*, u.nombre_completo as creador_nombre, u.imagen_perfil as creador_imagen, u.plan 
                FROM cursos c 
                INNER JOIN usuarios u ON c.creador_id = u.id 
                WHERE (u.plan = 'pro' OR u.plan = 'admin') AND c.estado = 'publico' 
                ORDER BY c.fecha_creacion DESC LIMIT 3";
        return $conexion->query($sql);
    }

    // Obtiene los cursos creados por usuarios GRATIS, con filtro opcional
    public static function obtenerRegulares($conexion, $busqueda = '') {
        if ($busqueda !== '') {
            $termino = "%" . $busqueda . "%";
            $sql = "SELECT DISTINCT c.*, u.nombre_completo as creador_nombre, u.imagen_perfil as creador_imagen, u.plan 
                    FROM cursos c 
                    INNER JOIN usuarios u ON c.creador_id = u.id 
                    LEFT JOIN curso_etiquetas ce ON c.id = ce.curso_id 
                    LEFT JOIN etiquetas e ON ce.etiqueta_id = e.id 
                    WHERE u.plan = 'gratis' 
                      AND c.estado = 'publico' 
                      AND (c.titulo LIKE ? OR e.nombre LIKE ?) 
                    ORDER BY c.fecha_creacion DESC";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ss", $termino, $termino);
            $stmt->execute();
            return $stmt->get_result();
        }

        $sql = "SELECT c.*, u.nombre_completo as creador_nombre, u.imagen_perfil as creador_imagen, u.plan 
                FROM cursos c 
                INNER JOIN usuarios u ON c.creador_id = u.id 
                WHERE u.plan = 'gratis' AND c.estado = 'publico' 
                ORDER BY c.fecha_creacion DESC";
        return $conexion->query($sql);
    }

    // Obtiene las etiquetas cruzando las tablas N:M
    public static function obtenerEtiquetas($conexion, $curso_id) {
        $sql = "SELECT e.nombre FROM etiquetas e 
                INNER JOIN curso_etiquetas ce ON e.id = ce.etiqueta_id 
                WHERE ce.curso_id = ?";
        $stmt = $conexion->prepare($sql);
        $etiquetas = [];
        if ($stmt) {
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $etiquetas[] = $fila['nombre'];
            }
            $stmt->close();
        }
        return $etiquetas;
    }

    // Obtener los cursos creados por un usuario específico
    public static function obtenerPorCreador($conexion, $creador_id) {
        $sql = "SELECT * FROM cursos WHERE creador_id = ? ORDER BY fecha_creacion DESC";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $creador_id);
            $stmt->execute();
            return $stmt->get_result();
        }
        return false;
    }

    // Obtener los cursos a los que el usuario está inscrito
    public static function obtenerInscritos($conexion, $usuario_id) {
        $sql = "SELECT c.*, i.fecha_inscripcion 
                FROM cursos c 
                INNER JOIN inscripciones i ON c.id = i.curso_id 
                WHERE i.usuario_id = ? AND i.estado = 'activa'
                ORDER BY i.fecha_inscripcion DESC";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            return $stmt->get_result();
        }
        return false;
    }

    // Obtener la lista de alumnos inscritos en un curso específico
    public static function obtenerAlumnosPorCurso($conexion, $curso_id) {
        $sql = "SELECT u.id, u.nombre_completo, u.email, u.imagen_perfil, i.fecha_inscripcion 
                FROM inscripciones i 
                INNER JOIN usuarios u ON i.usuario_id = u.id 
                WHERE i.curso_id = ? AND i.estado = 'activa' 
                ORDER BY i.fecha_inscripcion DESC";
        $stmt = $conexion->prepare($sql);
        $alumnos = [];
        if ($stmt) {
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $alumnos[] = $fila;
            }
            $stmt->close();
        }
        return $alumnos;
    }

    // Cuenta cuántos cursos ha creado un usuario específico
    public static function contarPorCreador($conexion, $creador_id) {
        $sql = "SELECT COUNT(*) as total FROM cursos WHERE creador_id = ?";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $creador_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $fila = $resultado->fetch_assoc();
            $stmt->close();
            return intval($fila['total']);
        }
        return 0;
    }
}
?>