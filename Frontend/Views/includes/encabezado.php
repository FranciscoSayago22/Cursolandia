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
            <a class="nav-link" href="#">Planes (Gratis vs PRO)</a>
          </li>
        </ul>

        <!-- Acceso / Autenticación y Botón de Tema -->
        <div class="d-flex gap-2 align-items-center">
          <a href="#" class="btn btn-outline-light btn-sm">Iniciar Sesión</a>
          <a href="registro.php" class="btn btn-primary btn-sm">Registrarse</a>
          <!-- Botón de Modo Oscuro/Claro -->
          <button id="themeToggle" class="btn btn-outline-secondary btn-sm ms-2" title="Cambiar tema">
            <i class="bi bi-moon-fill"></i>
          </button>
        </div>
      </div>
    </div>
  </nav>