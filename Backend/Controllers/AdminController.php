<?php
session_start();

// PROTECCIÓN ESTRICTA: Solo administradores pueden ejecutar este archivo
if (!isset($_SESSION['usuario_id']) || $_SESSION['plan'] !== 'admin') {
    header("Location: ../../Frontend/Views/index.php");
    exit();
}

require_once '../Config/database.php';

// Verificamos que lleguen todos los datos necesarios desde el formulario
if (isset($_POST['accion']) && isset($_POST['solicitud_id']) && isset($_POST['usuario_id'])) {
    
    $db = new Conexion();
    $conexion = $db->conectar();
    
    $accion = $_POST['accion'];
    $solicitud_id = intval($_POST['solicitud_id']); // Sanitizamos convirtiendo a entero
    $usuario_id = intval($_POST['usuario_id']);
    
    if ($accion === 'aprobar') {
        // 1. Actualizar el estado de la solicitud a 'aprobada'
        $sql_solicitud = "UPDATE solicitudes_pro SET estado = 'aprobada' WHERE id = ?";
        $stmt1 = $conexion->prepare($sql_solicitud);
        $stmt1->bind_param("i", $solicitud_id);
        $stmt1->execute();
        $stmt1->close();
        
        // 2. Actualizar el plan del usuario a 'pro'
        // Extra: Usamos DATE_ADD de MySQL para darle 1 año de vigencia al plan desde hoy
        $sql_usuario = "UPDATE usuarios SET plan = 'pro', vencimiento_pro = DATE_ADD(CURRENT_DATE, INTERVAL 1 YEAR) WHERE id = ?";
        $stmt2 = $conexion->prepare($sql_usuario);
        $stmt2->bind_param("i", $usuario_id);
        $stmt2->execute();
        $stmt2->close();
        
        header("Location: ../../Frontend/Views/admin_solicitudes.php?exito=aprobada");
        exit();
        
    } elseif ($accion === 'rechazar') {
        // Solo actualizamos la solicitud a 'rechazada', el usuario sigue siendo 'gratis'
        $sql_solicitud = "UPDATE solicitudes_pro SET estado = 'rechazada' WHERE id = ?";
        $stmt = $conexion->prepare($sql_solicitud);
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
        $stmt->close();
        
        header("Location: ../../Frontend/Views/admin_solicitudes.php?exito=rechazada");
        exit();
    }
}

// Si alguien intenta entrar al controlador sin enviar el formulario POST
header("Location: ../../Frontend/Views/admin_solicitudes.php");
exit();
?>