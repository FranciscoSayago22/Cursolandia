<?php
session_start();
require_once '../Config/database.php';
require_once '../Models/Inscripcion.php';

// Verificamos que el usuario esté logueado y haya enviado el formulario
if (!isset($_SESSION['usuario_id']) || !isset($_POST['inscribirse'])) {
    header("Location: ../../Frontend/Views/index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$curso_id = intval($_POST['curso_id']);
$es_pro = (isset($_SESSION['plan']) && ($_SESSION['plan'] === 'pro' || $_SESSION['plan'] === 'admin'));

$db = new Conexion();
$conexion = $db->conectar();

// 1. Verificar si el curso existe y si tiene cupo máximo disponible
$sql_curso = "SELECT cupo, creador_id FROM cursos WHERE id = ?";
$stmt_curso = $conexion->prepare($sql_curso);
$stmt_curso->bind_param("i", $curso_id);
$stmt_curso->execute();
$datos_curso = $stmt_curso->get_result()->fetch_assoc();
$stmt_curso->close();

if (!$datos_curso || $datos_curso['creador_id'] == $usuario_id) {
    header("Location: ../../Frontend/Views/index.php");
    exit();
}

if ($datos_curso['cupo'] !== null) {
    $inscritos_actuales = Inscripcion::contarPorCurso($conexion, $curso_id);
    if ($inscritos_actuales >= intval($datos_curso['cupo'])) {
        header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "&error=sin_cupo");
        exit();
    }
}

// REGLA DE NEGOCIO: Si es plan gratuito, no puede tener más de 3 cursos activos
if (!$es_pro) {
    $cursos_activos = Inscripcion::contarActivas($conexion, $usuario_id);
    
    if ($cursos_activos >= 3) {
        // Alcanzó el límite, lo devolvemos con un mensaje de error
        header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "&error=limite_cursos");
        exit();
    }
}

// Intentamos registrar la inscripción
// Si falla, suele ser porque la restricción UNIQUE de la BD detectó que ya estaba inscrito
if (Inscripcion::registrar($conexion, $usuario_id, $curso_id)) {
    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "&exito=inscrito");
} else {
    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "&error=ya_inscrito");
}
exit();
?>