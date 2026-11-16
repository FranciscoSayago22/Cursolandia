<?php
session_start();
// Requerimos la configuración y el modelo
require_once '../Config/database.php';
require_once '../Models/Usuario.php';

// Verificamos si se envió el formulario (por el botón submit)
if (isset($_POST['registrar_usuario'])) {
    
    // 1. Recibir y limpiar datos de texto
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $titulo = isset($_POST['titulo_profesional']) ? trim($_POST['titulo_profesional']) : null;
    $antecedentes = isset($_POST['antecedentes']) ? trim($_POST['antecedentes']) : null;
    
    // Encriptamos la contraseña de forma segura (Nativo de PHP)
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // 2. Manejo de Archivos (Subida de imagen y comprobante)
    $directorio_destino = "../../Resources/Uploads/";
    
    // Si la carpeta Uploads no existe, la crea
    if (!file_exists($directorio_destino)) {
        mkdir($directorio_destino, 0777, true);
    }

    $ruta_imagen = "";
    // Verificamos si se subió la imagen sin errores
    if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] == UPLOAD_ERR_OK) {
        // Le agregamos time() al nombre para que nunca se repita y sobreescriba
        $nombre_imagen = time() . "_" . basename($_FILES['imagen_perfil']['name']);
        $ruta_imagen = $nombre_imagen;
        move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $directorio_destino . $nombre_imagen);
    }

    $ruta_comprobante = null;
    // Verificamos si se subió un comprobante de título
    if (isset($_FILES['comprobante_titulo']) && $_FILES['comprobante_titulo']['error'] == UPLOAD_ERR_OK) {
        $nombre_comprobante = time() . "_doc_" . basename($_FILES['comprobante_titulo']['name']);
        $ruta_comprobante = $nombre_comprobante;
        move_uploaded_file($_FILES['comprobante_titulo']['tmp_name'], $directorio_destino . $nombre_comprobante);
    }

    // 3. Conexión a la Base de Datos 
    $db = new Conexion();
    $conexion = $db->conectar();

    // 4. Instanciamos el Modelo Usuario
    $usuario = new Usuario($nombre_completo, $email, $password_hashed);
    
    // Usamos el método __set para asignar los datos que pueden ser nulos o archivos
    $usuario->imagen_perfil = $ruta_imagen;
    $usuario->titulo_profesional = $titulo;
    $usuario->comprobante_titulo = $ruta_comprobante;
    $usuario->antecedentes = $antecedentes;

    // 5. Guardar en la Base de Datos
    $resultado = $usuario->guardar($conexion);

    if ($resultado === true) {
        header("Location: ../../Frontend/Views/login.php?registro=exito");
        exit();
    } else {
        // Borramos los archivos que se subieron para no dejar basura en el servidor
        if (!empty($ruta_imagen) && file_exists($directorio_destino . $ruta_imagen)) {
            unlink($directorio_destino . $ruta_imagen);
        }
        if (!empty($ruta_comprobante) && file_exists($directorio_destino . $ruta_comprobante)) {
            unlink($directorio_destino . $ruta_comprobante);
        }

        if ($resultado === 'duplicado') {
            header("Location: ../../Frontend/Views/registro.php?error=email_duplicado");
        } else {
            header("Location: ../../Frontend/Views/registro.php?error=db");
        }
        exit();
    }

}elseif (isset($_POST['login_usuario'])) {
    
    // 1. Recibir credenciales
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. Conexión a la BD
    $db = new Conexion();
    $conexion = $db->conectar();

    // 3. Autenticar usando el método del Modelo
    $usuarioData = Usuario::autenticar($conexion, $email, $password);

    if ($usuarioData) {
        // 4. Iniciar sesión y guardar variables globales
        $_SESSION['usuario_id'] = $usuarioData['id'];
        $_SESSION['nombre_completo'] = $usuarioData['nombre_completo'];
        $_SESSION['imagen_perfil'] = $usuarioData['imagen_perfil'];
        $_SESSION['plan'] = $usuarioData['plan'];

        // Redirigir al inicio
        header("Location: ../../Frontend/Views/index.php");
        exit();
    } else {
        // Credenciales inválidas
        header("Location: ../../Frontend/Views/login.php?error=credenciales");
        exit();
    }

} elseif (isset($_GET['logout'])) {
    // Cerrar sesión y destruir variables
    session_destroy();
    header("Location: ../../Frontend/Views/index.php");
    exit();

} else {
    // Si acceden al controlador sin peticiones válidas
    header("Location: ../../Frontend/Views/registro.php");
    exit();
}
?>