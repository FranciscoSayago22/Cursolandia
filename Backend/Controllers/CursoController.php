<?php
session_start();
require_once '../Config/database.php';
require_once '../Models/Curso.php';

if (!isset($_SESSION['usuario_id']) || (!isset($_POST['crear_curso']) && !isset($_POST['editar_curso']))) {
    header("Location: ../../Frontend/Views/index.php");
    exit();
}

$creador_id = $_SESSION['usuario_id'];
$es_pro = (isset($_SESSION['plan']) && ($_SESSION['plan'] === 'pro' || $_SESSION['plan'] === 'admin'));

// Conexión a la base de datos
$db = new Conexion();
$conexion = $db->conectar();

// =========================================================================
// ACCIÓN 1: CREAR UN NUEVO CURSO
// =========================================================================
if (isset($_POST['crear_curso'])) {

    // REGLA DE NEGOCIO: Usuario gratis máximo 3 cursos creados
    if (!$es_pro) {
        $cursos_creados = Curso::contarPorCreador($conexion, $creador_id);
        if ($cursos_creados >= 3) {
            header("Location: ../../Frontend/Views/crear_curso.php?error=limite_creacion");
            exit();
        }
    }

    // 1. Recibir datos del formulario
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $etiquetas = trim($_POST['etiquetas']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $hoy = date('Y-m-d');

    // Validar que las fechas sean coherentes (inicio >= hoy y fin >= inicio)
    if (empty($fecha_inicio) || empty($fecha_fin) || $fecha_inicio < $hoy || $fecha_fin < $fecha_inicio) {
        header("Location: ../../Frontend/Views/crear_curso.php?error=fechas_invalidas");
        exit();
    }

    $costo = floatval($_POST['costo']);
    $cupo = trim($_POST['cupo']);
    $estado = $_POST['estado'];

    // 2. Lógica de Subida Múltiple y Validación de Límites
    $archivos_validados = [];
    $directorio_destino = "../../Resources/Uploads/";

    if (isset($_FILES['material_curso']) && $_FILES['material_curso']['name'][0] != "") {
        
        $cantidad_archivos = count($_FILES['material_curso']['name']);
        $peso_total = 0;
        
        // Constantes en bytes (1MB = 1048576 Bytes)
        $LIMITE_POR_ARCHIVO = 5 * 1048576; // 5MB
        $LIMITE_TOTAL = 10 * 1048576;      // 10MB

        // Regla 1: Usuario gratis máximo 5 archivos
        if (!$es_pro && $cantidad_archivos > 5) {
            header("Location: ../../Frontend/Views/crear_curso.php?error=limite_archivos#seccion-material");
            exit();
        }

        // Calculamos el peso total y validamos peso por archivo
        for ($i = 0; $i < $cantidad_archivos; $i++) {
            $peso_archivo = $_FILES['material_curso']['size'][$i];
            
            if (!$es_pro && $peso_archivo > $LIMITE_POR_ARCHIVO) {
                header("Location: ../../Frontend/Views/crear_curso.php?error=peso_individual#seccion-material");
                exit();
            }
            $peso_total += $peso_archivo;
        }

        // Regla 2: Usuario gratis máximo 10MB en total
        if (!$es_pro && $peso_total > $LIMITE_TOTAL) {
            header("Location: ../../Frontend/Views/crear_curso.php?error=peso_total#seccion-material");
            exit();
        }

        // Si pasó las validaciones matemáticas, procesamos (movemos) los archivos
        for ($i = 0; $i < $cantidad_archivos; $i++) {
            if ($_FILES['material_curso']['error'][$i] == UPLOAD_ERR_OK) {
                $nombre_original = basename($_FILES['material_curso']['name'][$i]);
                $nombre_unico = time() . "_" . uniqid() . "_" . $nombre_original;
                $peso = $_FILES['material_curso']['size'][$i];
                $ruta_temp = $_FILES['material_curso']['tmp_name'][$i];
                
                if (move_uploaded_file($ruta_temp, $directorio_destino . $nombre_unico)) {
                    $archivos_validados[] = [
                        'nombre' => $nombre_unico,
                        'peso' => $peso
                    ];
                }
            }
        }
    }

    // 3. Llamamos al Modelo Transaccional
    $exito = Curso::crear($conexion, $creador_id, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $estado, $etiquetas, $archivos_validados);

    if ($exito) {
        header("Location: ../../Frontend/Views/index.php?exito=curso_creado");
    } else {
        header("Location: ../../Frontend/Views/crear_curso.php?error=db");
    }
    exit();
}

// =========================================================================
// ACCIÓN 2: EDITAR UN CURSO EXISTENTE
// =========================================================================
elseif (isset($_POST['editar_curso'])) {
    $curso_id = intval($_POST['curso_id']);
    $curso_actual = Curso::obtenerPorId($conexion, $curso_id);

    // Verificamos que el curso exista y pertenezca al usuario logueado
    if (!$curso_actual || intval($curso_actual['creador_id']) !== intval($creador_id)) {
        header("Location: ../../Frontend/Views/index.php");
        exit();
    }

    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $etiquetas = trim($_POST['etiquetas']);
    $estado = ($_POST['estado'] === 'privado') ? 'privado' : 'publico';
    $ids_eliminar = isset($_POST['eliminar_materiales']) && is_array($_POST['eliminar_materiales']) ? $_POST['eliminar_materiales'] : [];

    // Calculamos cuántos archivos y bytes quedarán después de borrar los marcados
    $materiales_existentes = Curso::obtenerMateriales($conexion, $curso_id);
    $cantidad_restante = 0;
    $peso_restante = 0;

    foreach ($materiales_existentes as $mat) {
        if (!in_array($mat['id'], $ids_eliminar)) {
            $cantidad_restante++;
            $peso_restante += intval($mat['peso_bytes']);
        }
    }

    $archivos_nuevos = [];
    $directorio_destino = "../../Resources/Uploads/";

    if (isset($_FILES['material_curso']) && $_FILES['material_curso']['name'][0] != "") {
        $cantidad_subida = count($_FILES['material_curso']['name']);
        $peso_subida = 0;

        $LIMITE_POR_ARCHIVO = 5 * 1048576; // 5MB
        $LIMITE_TOTAL = 10 * 1048576;      // 10MB

        // Regla 1: Total de archivos (restantes + nuevos) <= 5 para plan gratuito
        if (!$es_pro && ($cantidad_restante + $cantidad_subida) > 5) {
            header("Location: ../../Frontend/Views/editar_curso.php?id=" . $curso_id . "&error=limite_archivos#seccion-material");
            exit();
        }

        for ($i = 0; $i < $cantidad_subida; $i++) {
            $peso_archivo = $_FILES['material_curso']['size'][$i];
            if (!$es_pro && $peso_archivo > $LIMITE_POR_ARCHIVO) {
                header("Location: ../../Frontend/Views/editar_curso.php?id=" . $curso_id . "&error=peso_individual#seccion-material");
                exit();
            }
            $peso_subida += $peso_archivo;
        }

        // Regla 2: Peso total (restante + nuevo) <= 10MB para plan gratuito
        if (!$es_pro && ($peso_restante + $peso_subida) > $LIMITE_TOTAL) {
            header("Location: ../../Frontend/Views/editar_curso.php?id=" . $curso_id . "&error=peso_total#seccion-material");
            exit();
        }

        for ($i = 0; $i < $cantidad_subida; $i++) {
            if ($_FILES['material_curso']['error'][$i] == UPLOAD_ERR_OK) {
                $nombre_original = basename($_FILES['material_curso']['name'][$i]);
                $nombre_unico = time() . "_" . uniqid() . "_" . $nombre_original;
                $peso = $_FILES['material_curso']['size'][$i];
                $ruta_temp = $_FILES['material_curso']['tmp_name'][$i];

                if (move_uploaded_file($ruta_temp, $directorio_destino . $nombre_unico)) {
                    $archivos_nuevos[] = [
                        'nombre' => $nombre_unico,
                        'peso' => $peso
                    ];
                }
            }
        }
    }

    $exito = Curso::actualizar($conexion, $curso_id, $creador_id, $titulo, $descripcion, $estado, $etiquetas, $ids_eliminar, $archivos_nuevos, $directorio_destino);

    if ($exito) {
        header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "&exito=curso_editado");
    } else {
        // Si falló la BD, limpiamos los archivos recién subidos
        foreach ($archivos_nuevos as $nuevo) {
            if (file_exists($directorio_destino . $nuevo['nombre'])) {
                unlink($directorio_destino . $nuevo['nombre']);
            }
        }
        header("Location: ../../Frontend/Views/editar_curso.php?id=" . $curso_id . "&error=db");
    }
    exit();
}
?>