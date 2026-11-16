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
$usuario_id = intval($_SESSION['usuario_id']);

// 1. NUEVA PREGUNTA
if (isset($_POST['nueva_pregunta'])) {
    $curso_id = intval($_POST['curso_id']);
    $pregunta = trim($_POST['pregunta']);

    if (!empty($pregunta)) {
        Foro::crearPregunta($conexion, $curso_id, $usuario_id, $pregunta);
    }
    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "#foro");
    exit();
}

// 2. NUEVA RESPUESTA
elseif (isset($_POST['nueva_respuesta'])) {
    $curso_id = intval($_POST['curso_id']);
    $pregunta_id = intval($_POST['pregunta_id']);
    $respuesta = trim($_POST['respuesta']);

    if (!empty($respuesta)) {
        $res = Foro::crearRespuesta($conexion, $pregunta_id, $usuario_id, $respuesta);
        if ($res === 'respuesta_seguida') {
            header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "&error=respuesta_seguida#foro");
            exit();
        } elseif ($res === 'cerrada') {
            header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "&error=pregunta_cerrada#foro");
            exit();
        }
    }
    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "#foro");
    exit();
}

// 2b. EDITAR ÚLTIMA RESPUESTA
elseif (isset($_POST['editar_respuesta'])) {
    $curso_id = intval($_POST['curso_id']);
    $pregunta_id = intval($_POST['pregunta_id']);
    $respuesta_id = intval($_POST['respuesta_id']);
    $nuevo_texto = trim($_POST['respuesta']);

    if (!empty($nuevo_texto)) {
        Foro::editarRespuesta($conexion, $respuesta_id, $pregunta_id, $usuario_id, $nuevo_texto);
    }
    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "#foro");
    exit();
}

// 3. DESTACAR RESPUESTA
elseif (isset($_POST['marcar_aceptada'])) {
    $curso_id = intval($_POST['curso_id']);
    $pregunta_id = intval($_POST['pregunta_id']);
    $respuesta_id = intval($_POST['respuesta_id']);

    Foro::aceptarRespuesta($conexion, $respuesta_id, $pregunta_id, $usuario_id);

    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "#foro");
    exit();
}

// 4. CERRAR PREGUNTA
elseif (isset($_POST['cerrar_pregunta'])) {
    $curso_id = intval($_POST['curso_id']);
    $pregunta_id = intval($_POST['pregunta_id']);

    Foro::cerrarPregunta($conexion, $pregunta_id, $usuario_id);

    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "#foro");
    exit();
}

// 5. ELIMINAR PREGUNTA
elseif (isset($_POST['eliminar_pregunta'])) {
    $curso_id = intval($_POST['curso_id']);
    $pregunta_id = intval($_POST['pregunta_id']);
    $es_admin = (isset($_SESSION['plan']) && $_SESSION['plan'] === 'admin');

    Foro::eliminarPregunta($conexion, $pregunta_id, $usuario_id, $es_admin);

    header("Location: ../../Frontend/Views/curso.php?id=" . $curso_id . "#foro");
    exit();
}

else {
    header("Location: ../../Frontend/Views/index.php");
    exit();
}
?>