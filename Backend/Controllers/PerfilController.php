<?php
session_start();
require_once '../Config/database.php';
require_once '../Models/Usuario.php';

// Si no está logueado, lo expulsamos
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../Frontend/Views/login.php");
    exit();
}

$db = new Conexion();
$conexion = $db->conectar();
$usuario_id = $_SESSION['usuario_id'];
$directorio_destino = "../../Resources/Uploads/";

// ==========================================
// 1. ACTUALIZAR DATOS PERSONALES
// ==========================================
if (isset($_POST['actualizar_perfil'])) {
    $nombre_completo = trim($_POST['nombre_completo']);
    $titulo = isset($_POST['titulo_profesional']) ? trim($_POST['titulo_profesional']) : null;
    $antecedentes = isset($_POST['antecedentes']) ? trim($_POST['antecedentes']) : null;
    $intereses = isset($_POST['intereses']) ? trim($_POST['intereses']) : null;

    $ruta_imagen = null;
    $ruta_comprobante = null;

    // Verificar si subió una NUEVA imagen
    if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] == UPLOAD_ERR_OK) {
        $ruta_imagen = time() . "_avatar_" . basename($_FILES['nueva_imagen']['name']);
        move_uploaded_file($_FILES['nueva_imagen']['tmp_name'], $directorio_destino . $ruta_imagen);
        
        // Actualizamos la variable de sesión para que cambie en el menú de arriba al instante
        $_SESSION['imagen_perfil'] = $ruta_imagen; 
    }

    // Verificar si subió un NUEVO comprobante de título
    if (isset($_FILES['comprobante_titulo']) && $_FILES['comprobante_titulo']['error'] == UPLOAD_ERR_OK) {
        $ruta_comprobante = time() . "_doc_" . basename($_FILES['comprobante_titulo']['name']);
        move_uploaded_file($_FILES['comprobante_titulo']['tmp_name'], $directorio_destino . $ruta_comprobante);
    }

    // Actualizamos el nombre en sesión por si lo cambió
    $_SESSION['nombre_completo'] = $nombre_completo;

    // Llamamos al método del modelo
    if (Usuario::actualizarDatos($conexion, $usuario_id, $nombre_completo, $titulo, $antecedentes, $ruta_imagen, $ruta_comprobante)) {
        header("Location: ../../Frontend/Views/perfil.php?exito=datos");
    } else {
        header("Location: ../../Frontend/Views/perfil.php?error=db");
    }
    exit();
}

// ==========================================
// 2. CAMBIAR CONTRASEÑA
// ==========================================
elseif (isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmacion = $_POST['password_confirmacion'];

    if ($password_nueva !== $password_confirmacion) {
        header("Location: ../../Frontend/Views/perfil.php?error=password_mismatch");
        exit();
    }

    // Llamamos al método del modelo para verificar y cambiar
    if (Usuario::cambiarPassword($conexion, $usuario_id, $password_actual, $password_nueva)) {
        header("Location: ../../Frontend/Views/perfil.php?exito=password");
    } else {
        header("Location: ../../Frontend/Views/perfil.php?error=password_incorrecta");
    }
    exit();
}

// ==========================================
// 3. SOLICITAR PLAN PRO
// ==========================================
elseif (isset($_POST['solicitar_pro'])) {
    $mensaje = trim($_POST['mensaje_admin']);
    
    if (isset($_FILES['comprobante_pro']) && $_FILES['comprobante_pro']['error'] == UPLOAD_ERR_OK) {
        $nombre_pago = time() . "_pago_" . basename($_FILES['comprobante_pro']['name']);
        move_uploaded_file($_FILES['comprobante_pro']['tmp_name'], $directorio_destino . $nombre_pago);
        
        // Insertamos la solicitud en la base de datos
        $sql = "INSERT INTO solicitudes_pro (usuario_id, comprobante_pago, mensaje) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iss", $usuario_id, $nombre_pago, $mensaje);
            $stmt->execute();
            $stmt->close();
        }
        
        header("Location: ../../Frontend/Views/perfil.php?exito=pro_solicitado");
    } else {
        header("Location: ../../Frontend/Views/perfil.php?error=archivo_invalido");
    }
    exit();
}

else {
    header("Location: ../../Frontend/Views/perfil.php");
    exit();
}
?>