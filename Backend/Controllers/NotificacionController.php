<?php
ob_start();
session_start();
require_once '../Config/database.php';
require_once '../Models/Notificacion.php';

function redirigir($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo '<script>window.location.href = ' . json_encode($url) . ';</script>';
    }
    exit();
}

if (!isset($_SESSION['usuario_id']) || !isset($_GET['accion'])) {
    redirigir("../../Frontend/Views/index.php");
}

$usuario_id = intval($_SESSION['usuario_id']);
$accion = $_GET['accion'];
$volver = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "../../Frontend/Views/index.php";

$db = new Conexion();
$conexion = $db->conectar();

if ($accion === 'ir' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $notif = Notificacion::obtenerPorId($conexion, $id, $usuario_id);
    if ($notif) {
        Notificacion::marcarLeida($conexion, $id, $usuario_id);
        $destino = !empty($notif['enlace']) ? "../../Frontend/Views/" . $notif['enlace'] : "../../Frontend/Views/index.php";
        redirigir($destino);
    }
} elseif ($accion === 'leer_todas') {
    Notificacion::marcarTodasLeidas($conexion, $usuario_id);
} elseif ($accion === 'eliminar' && isset($_GET['id'])) {
    Notificacion::eliminar($conexion, intval($_GET['id']), $usuario_id);
} elseif ($accion === 'eliminar_leidas') {
    Notificacion::eliminarLeidas($conexion, $usuario_id);
}

redirigir($volver);
?>