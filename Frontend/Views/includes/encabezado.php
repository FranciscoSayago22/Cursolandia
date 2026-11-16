<?php
// Iniciar la sesión si aún no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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