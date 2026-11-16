<?php
session_start();
require_once '../Config/database.php';
require_once '../Models/Curso.php';

if (!isset($_SESSION['usuario_id']) || !isset($_POST['crear_curso'])) {
    header("Location: ../../Frontend/Views/index.php");
    exit();
}

$creador_id = $_SESSION['usuario_id'];
$es_pro = (isset($_SESSION['plan']) && ($_SESSION['plan'] === 'pro' || $_SESSION['plan'] === 'admin'));

$creador_id = $_SESSION['usuario_id'];
$es_pro = (isset($_SESSION['plan']) && ($_SESSION['plan'] === 'pro' || $_SESSION['plan'] === 'admin'));
// Conexión a la base de datos
$db = new Conexion();
$conexion = $db->conectar();

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

// Verificamos si se subieron archivos y si el primero no está vacío
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
            // Armamos un nombre único seguro
            $nombre_original = basename($_FILES['material_curso']['name'][$i]);
            $nombre_unico = time() . "_" . uniqid() . "_" . $nombre_original;
            $peso = $_FILES['material_curso']['size'][$i];
            $ruta_temp = $_FILES['material_curso']['tmp_name'][$i];
            
            if (move_uploaded_file($ruta_temp, $directorio_destino . $nombre_unico)) {
                // Guardamos en el array los datos limpios para mandarlos al Modelo
                $archivos_validados[] = [
                    'nombre' => $nombre_unico,
                    'peso' => $peso
                ];
            }
        }
    }
}


// Llamamos al Modelo Transaccional
$exito = Curso::crear($conexion, $creador_id, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $estado, $etiquetas, $archivos_validados);

if ($exito) {
    // Redirigir al inicio o a una página de "Mis Cursos"
    header("Location: ../../Frontend/Views/index.php?exito=curso_creado");
} else {
    header("Location: ../../Frontend/Views/crear_curso.php?error=db");
}
exit();
?>