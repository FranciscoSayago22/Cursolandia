<?php 
$clase_body = "d-flex flex-column min-vh-100 bg-body-secondary";
include 'includes/encabezado.php'; 
?>

  <main class="flex-grow-1 d-flex align-items-center py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          
          <!-- Alerta dinámica si viene de un registro exitoso -->
          <?php if(isset($_GET['registro']) && $_GET['registro'] == 'exito'): ?>
          <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>¡Registro completado! Ya puedes iniciar sesión.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php endif; ?>

          <div class="card shadow border-0 bg-body rounded-4">
            <div class="card-body p-4 p-md-5">
              <div class="text-center mb-4">
                <h2 class="fw-bold">Iniciar Sesión</h2>
                <p class="text-body-secondary">Ingresa a tu cuenta de Cursolandia.</p>
              </div>

              <!-- El action apuntará al mismo controlador -->
              <form action="../../Backend/Controllers/AuthController.php" method="POST">
                
                <div class="mb-3">
                  <label for="email" class="form-label fw-medium">Correo Electrónico</label>
                  <input type="email" class="form-control bg-body-tertiary" id="email" name="email" required>
                </div>
                
                <div class="mb-4">
                  <label for="password" class="form-label fw-medium">Contraseña</label>
                  <input type="password" class="form-control bg-body-tertiary" id="password" name="password" required>
                </div>

                <div class="d-grid mb-4">
                  <button type="submit" class="btn btn-primary btn-lg fw-bold" name="login_usuario">Ingresar</button>
                </div>

              </form>

              <div class="text-center border-top border-secondary-subtle pt-3">
                <span class="text-muted">¿No tienes cuenta?</span> 
                <a href="registro.php" class="text-primary text-decoration-none fw-medium">Regístrate aquí</a>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

<?php include 'includes/pie.php'; ?>