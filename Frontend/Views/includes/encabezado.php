<?php
// Iniciar la sesión si aún no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$notificaciones_nav = [];
$no_leidas_nav = 0;

if (isset($_SESSION['usuario_id'])) {
    require_once __DIR__ . '/../../../Backend/Config/database.php';
    require_once __DIR__ . '/../../../Backend/Models/Notificacion.php';

    if (!isset($conexion) || !($conexion instanceof mysqli)) {
        $db_nav = new Conexion();
        $conexion = $db_nav->conectar();
    }

    $no_leidas_nav = Notificacion::contarNoLeidas($conexion, $_SESSION['usuario_id']);
    $notificaciones_nav = Notificacion::obtenerPorUsuario($conexion, $_SESSION['usuario_id'], 15);
}
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cursolandia.com | Aprende y Enseña</title>
  <!-- Bootstrap 5 CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Estilos propios -->
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="<?php echo isset($clase_body) ? $clase_body : ''; ?>">

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold text-primary" href="index.php">
        <i class="bi bi-mortarboard-fill me-1"></i>Cursolandia
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Menu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="index.php">Explorar Cursos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="planes.php">Planes (Gratis vs PRO)</a>
          </li>

          <!-- Menú exclusivo para Administradores -->
          <?php if (isset($_SESSION['plan']) && $_SESSION['plan'] == 'admin'): ?>
          <li class="nav-item ms-lg-3">
            <a class="nav-link text-warning fw-bold" href="admin_solicitudes.php">
              <i class="bi bi-shield-lock-fill me-1"></i>Panel Admin
            </a>
          </li>
          <?php endif; ?>
        </ul>

        <!-- Acceso, Autenticación y Avatar -->
        <div class="d-flex gap-3 align-items-center">
          
          <?php if (isset($_SESSION['usuario_id'])): ?>
            <!-- Botón de Notificaciones -->
            <div class="dropdown me-2">
              <button class="btn btn-outline-secondary position-relative border-0 text-light" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notificaciones">
                <i class="bi bi-bell-fill fs-5"></i>
                <?php if ($no_leidas_nav > 0): ?>
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                    <?php echo $no_leidas_nav > 9 ? '9+' : $no_leidas_nav; ?>
                  </span>
                <?php endif; ?>
              </button>

              <div class="dropdown-menu dropdown-menu-end shadow-lg border-secondary-subtle p-0" style="width: 360px; max-width: 92vw;">
                <!-- Cabecera -->
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom border-secondary-subtle bg-body-tertiary rounded-top">
                  <span class="fw-bold small">
                    <i class="bi bi-bell me-1 text-primary"></i>Notificaciones
                    <?php if ($no_leidas_nav > 0): ?>
                      <span class="badge bg-primary rounded-pill ms-1"><?php echo $no_leidas_nav; ?></span>
                    <?php endif; ?>
                  </span>
                  <?php if ($no_leidas_nav > 0): ?>
                    <a href="../../Backend/Controllers/NotificacionController.php?accion=leer_todas" class="small text-decoration-none">
                      Marcar leídas
                    </a>
                  <?php endif; ?>
                </div>

                <!-- Lista de notificaciones -->
                <div style="max-height: 340px; overflow-y: auto;">
                  <?php if (count($notificaciones_nav) > 0): ?>
                    <div class="list-group list-group-flush">
                      <?php foreach ($notificaciones_nav as $n): ?>
                        <?php
                          $icono = 'bi-info-circle-fill text-primary';
                          if ($n['tipo'] === 'nueva_pregunta') $icono = 'bi-question-circle-fill text-info';
                          elseif ($n['tipo'] === 'nueva_respuesta') $icono = 'bi-chat-left-text-fill text-primary';
                          elseif ($n['tipo'] === 'respuesta_destacada') $icono = 'bi-award-fill text-warning';
                          elseif ($n['tipo'] === 'pregunta_cerrada') $icono = 'bi-lock-fill text-secondary';
                          elseif ($n['tipo'] === 'pregunta_eliminada') $icono = 'bi-trash3-fill text-danger';
                        ?>
                        <div class="list-group-item list-group-item-action d-flex align-items-start gap-2 py-2 px-3 <?php echo !$n['leida'] ? 'bg-primary-subtle bg-opacity-25' : ''; ?>">
                          <i class="bi <?php echo $icono; ?> mt-1 flex-shrink-0"></i>
                          <a href="../../Backend/Controllers/NotificacionController.php?accion=ir&id=<?php echo $n['id']; ?>" class="text-decoration-none text-body flex-grow-1 small">
                            <div class="<?php echo !$n['leida'] ? 'fw-semibold' : 'text-body-secondary'; ?>">
                              <?php echo htmlspecialchars($n['mensaje']); ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.7rem;">
                              <?php echo date("d/m/Y H:i", strtotime($n['fecha'])); ?>
                              <?php if (!$n['leida']): ?>
                                <span class="badge bg-primary ms-1" style="font-size: 0.6rem;">Nueva</span>
                              <?php endif; ?>
                            </div>
                          </a>
                          <a href="../../Backend/Controllers/NotificacionController.php?accion=eliminar&id=<?php echo $n['id']; ?>" class="text-muted small ms-1" title="Eliminar notificación">
                            <i class="bi bi-x-lg"></i>
                          </a>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <div class="text-center py-4 px-3 text-muted small">
                      <i class="bi bi-bell-slash fs-3 d-block mb-1"></i>
                      No tenés notificaciones por el momento.
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Pie -->
                <?php if (count($notificaciones_nav) > 0): ?>
                  <div class="px-3 py-2 border-top border-secondary-subtle bg-body-tertiary text-center rounded-bottom">
                    <a href="../../Backend/Controllers/NotificacionController.php?accion=eliminar_leidas" class="small text-danger text-decoration-none">
                      <i class="bi bi-trash me-1"></i>Limpiar notificaciones leídas
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Botón Crear Curso -->
            <a href="crear_curso.php" class="btn btn-primary btn-sm fw-medium me-2 shadow-sm">
              <i class="bi bi-plus-circle me-1"></i>Crear Curso
            </a>

            <!-- Botón Mis Cursos -->
            <a href="mis_cursos.php" class="btn btn-primary btn-sm fw-medium me-2 shadow-sm">
              <i class="bi bi-journal-bookmark-fill me-1"></i>Mis Cursos
            </a>
            <!-- Usuario Logueado: Dropdown con Avatar -->
            <div class="dropdown">
              <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                <?php if (!empty($_SESSION['imagen_perfil'])): ?>
                  <!-- Carga la imagen desde la carpeta Uploads y le da forma circular -->
                  <img src="../../Resources/Uploads/<?php echo htmlspecialchars($_SESSION['imagen_perfil']); ?>" alt="Avatar" width="35" height="35" class="rounded-circle object-fit-cover border border-2 border-primary">
                <?php else: ?>
                  <i class="bi bi-person-circle fs-3 text-secondary"></i>
                <?php endif; ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownUser">
                <li><h6 class="dropdown-header"><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></h6></li>
                <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../../Backend/Controllers/AuthController.php?logout=true">Cerrar Sesión</a></li>
              </ul>
            </div>
          <?php else: ?>
            <!-- Usuario NO Logueado: Botones y signo de interrogación -->
            <div class="d-flex gap-2 align-items-center">
              <a href="login.php" class="btn btn-outline-light btn-sm">Iniciar Sesión</a>
              <a href="registro.php" class="btn btn-primary btn-sm">Registrarse</a>
              <i class="bi bi-question-circle fs-4 text-secondary ms-1" title="Invitado"></i>
            </div>
          <?php endif; ?>

          <!-- Botón de Modo Oscuro/Claro -->
          <button id="themeToggle" class="btn btn-outline-secondary btn-sm" title="Cambiar tema">
            <i class="bi bi-moon-fill"></i>
          </button>
        </div>
      </div>
    </div>
  </nav>