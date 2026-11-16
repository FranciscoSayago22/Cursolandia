<?php
session_start();
require_once '../Config/database.php';
require_once '../Models/Foro.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../Frontend/Views/index.php");
    exit();
}

$db = new Conexion();
$conexion = $db->conectar();
$usuario_id = $_SESSION['usuario_id'];

// 1. NUEVA PREGUNTA
if (isset($_POST['nueva_pregunta'])) {
    $curso_id = intval($_POST['curso_id']);
    $pregunta = trim($_POST['pregunta']);

    if (!empty($pregunta)) {
        Foro::crearPregunta($conexion, $curso_id, $usuario_id, $pregunta);
    }
    // Redirigimos al curso agregando #foro al final para que la pantalla baje automáticamente hasta esa sección
    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "#foro");
    exit();
}

// 2. NUEVA RESPUESTA
elseif (isset($_POST['nueva_respuesta'])) {
    $curso_id = intval($_POST['curso_id']); // Lo pasamos oculto para poder redirigir de vuelta
    $pregunta_id = intval($_POST['pregunta_id']);
    $respuesta = trim($_POST['respuesta']);

    if (!empty($respuesta)) {
        Foro::crearRespuesta($conexion, $pregunta_id, $usuario_id, $respuesta);
    }
    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "#foro");
    exit();
}

// 3. ACEPTAR RESPUESTA (Solo el creador del curso puede hacerlo)
elseif (isset($_POST['marcar_aceptada'])) {
    $curso_id = intval($_POST['curso_id']);
    $pregunta_id = intval($_POST['pregunta_id']);
    $respuesta_id = intval($_POST['respuesta_id']);
    
    Foro::aceptarRespuesta($conexion, $respuesta_id, $pregunta_id);
    
    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "#foro");
    exit();
}

else {
    header("Location: ../../Frontend/Views/index.php");
    exit();
}
?>