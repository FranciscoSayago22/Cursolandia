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

    // Obtener un curso por su ID
    public static function obtenerPorId($conexion, $curso_id) {
        $sql = "SELECT * FROM cursos WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $curso = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $curso;
        }
        return null;
    }

    // Obtener los materiales adjuntos de un curso (incluyendo su ID)
    public static function obtenerMateriales($conexion, $curso_id) {
        $sql = "SELECT id, nombre_archivo, peso_bytes FROM material_curso WHERE curso_id = ?";
        $stmt = $conexion->prepare($sql);
        $materiales = [];
        if ($stmt) {
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $materiales[] = $fila;
            }
            $stmt->close();
        }
        return $materiales;
    }

    // Actualizar solo los campos permitidos del curso, etiquetas y materiales
    public static function actualizar($conexion, $curso_id, $creador_id, $titulo, $descripcion, $estado, $etiquetas, $ids_eliminar, $archivos_nuevos, $directorio_destino) {
        $archivos_a_borrar_fisico = [];

        try {
            $conexion->begin_transaction();

            // 1. Actualizar datos editables (nunca se tocan costo, cupo, fecha_inicio ni fecha_fin)
            $sql_update = "UPDATE cursos SET titulo = ?, descripcion = ?, estado = ? WHERE id = ? AND creador_id = ?";
            $stmt = $conexion->prepare($sql_update);
            $stmt->bind_param("sssii", $titulo, $descripcion, $estado, $curso_id, $creador_id);
            $stmt->execute();
            $stmt->close();

            // 2. Actualizar Etiquetas (limpiamos las anteriores y reinsertamos)
            $sql_del_tags = "DELETE FROM curso_etiquetas WHERE curso_id = ?";
            $stmt_del_tags = $conexion->prepare($sql_del_tags);
            $stmt_del_tags->bind_param("i", $curso_id);
            $stmt_del_tags->execute();
            $stmt_del_tags->close();

            if (!empty($etiquetas)) {
                $array_etiquetas = array_unique(array_filter(array_map('trim', explode(',', $etiquetas))));
                foreach ($array_etiquetas as $nombre_etiqueta) {
                    $etiqueta_id = null;

                    $sql_check = "SELECT id FROM etiquetas WHERE nombre = ?";
                    $stmt_check = $conexion->prepare($sql_check);
                    $stmt_check->bind_param("s", $nombre_etiqueta);
                    $stmt_check->execute();
                    $resultado = $stmt_check->get_result();

                    if ($fila = $resultado->fetch_assoc()) {
                        $etiqueta_id = $fila['id'];
                    } else {
                        $sql_insert_tag = "INSERT INTO etiquetas (nombre) VALUES (?)";
                        $stmt_tag = $conexion->prepare($sql_insert_tag);
                        $stmt_tag->bind_param("s", $nombre_etiqueta);
                        $stmt_tag->execute();
                        $etiqueta_id = $conexion->insert_id;
                        $stmt_tag->close();
                    }
                    $stmt_check->close();

                    $sql_pivot = "INSERT INTO curso_etiquetas (curso_id, etiqueta_id) VALUES (?, ?)";
                    $stmt_pivot = $conexion->prepare($sql_pivot);
                    $stmt_pivot->bind_param("ii", $curso_id, $etiqueta_id);
                    $stmt_pivot->execute();
                    $stmt_pivot->close();
                }
            }

            // 3. Eliminar materiales marcados por el educador
            if (!empty($ids_eliminar)) {
                $sql_sel_mat = "SELECT nombre_archivo FROM material_curso WHERE id = ? AND curso_id = ?";
                $sql_del_mat = "DELETE FROM material_curso WHERE id = ? AND curso_id = ?";
                $stmt_sel_mat = $conexion->prepare($sql_sel_mat);
                $stmt_del_mat = $conexion->prepare($sql_del_mat);

                foreach ($ids_eliminar as $mat_id) {
                    $mat_id = intval($mat_id);
                    $stmt_sel_mat->bind_param("ii", $mat_id, $curso_id);
                    $stmt_sel_mat->execute();
                    $res_mat = $stmt_sel_mat->get_result();

                    if ($fila_mat = $res_mat->fetch_assoc()) {
                        $archivos_a_borrar_fisico[] = $fila_mat['nombre_archivo'];
                        $stmt_del_mat->bind_param("ii", $mat_id, $curso_id);
                        $stmt_del_mat->execute();
                    }
                }
                $stmt_sel_mat->close();
                $stmt_del_mat->close();
            }

            // 4. Insertar nuevos materiales adjuntos
            if (!empty($archivos_nuevos)) {
                $sql_material = "INSERT INTO material_curso (curso_id, nombre_archivo, peso_bytes) VALUES (?, ?, ?)";
                $stmt_mat = $conexion->prepare($sql_material);
                foreach ($archivos_nuevos as $archivo) {
                    $stmt_mat->bind_param("isi", $curso_id, $archivo['nombre'], $archivo['peso']);
                    $stmt_mat->execute();
                }
                $stmt_mat->close();
            }

            $conexion->commit();

            // Borramos del disco los archivos eliminados solo después de confirmar en la BD
            foreach ($archivos_a_borrar_fisico as $archivo_viejo) {
                $ruta_fisica = $directorio_destino . $archivo_viejo;
                if (!empty($archivo_viejo) && file_exists($ruta_fisica)) {
                    unlink($ruta_fisica);
                }
            }

            return true;

        } catch (Exception $e) {
            $conexion->rollback();
            return false;
        }
    }
}
?>