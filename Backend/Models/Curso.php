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
}
?>