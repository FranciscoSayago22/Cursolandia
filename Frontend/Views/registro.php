<?php 
// Pasamos la clase específica que necesita la vista de registro
$clase_body = "d-flex flex-column min-vh-100 bg-body-secondary";
include 'includes/encabezado.php'; 
?>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="flex-grow-1 d-flex align-items-center py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
          <div class="card shadow border-0 bg-body rounded-4">
            <div class="card-body p-4 p-md-5">
              <div class="text-center mb-4">
                <h2 class="fw-bold">Crea tu cuenta</h2>
                <p class="text-body-secondary">Completa tu perfil para empezar a crear y tomar cursos en la plataforma.</p>
              </div>
              
              <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] === 'email_duplicado'): ?>
                  <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>No se puede registrar porque ese correo electrónico ya existe.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                <?php elseif ($_GET['error'] === 'db'): ?>
                  <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Ocurrió un error al crear la cuenta. Inténtalo nuevamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                <?php endif; ?>
              <?php endif; ?>

              <!-- El action apunta al Controlador -->
              <form action="../../Backend/Controllers/AuthController.php" method="POST" enctype="multipart/form-data">
                
                <!-- SECCIÓN: CREDENCIALES -->
                <h5 class="mb-3 border-bottom border-secondary-subtle pb-2 text-primary">Datos de Acceso</h5>
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <label for="email" class="form-label fw-medium">Correo Electrónico</label>
                    <input type="email" class="form-control bg-body-tertiary" id="email" name="email" placeholder="nombre@ejemplo.com" required>
                  </div>
                  <div class="col-md-6">
                    <label for="password" class="form-label fw-medium">Contraseña</label>
                    <input type="password" class="form-control bg-body-tertiary" id="password" name="password" required>
                  </div>
                </div>

                <!-- SECCIÓN: PERFIL PERSONAL -->
                <h5 class="mb-3 border-bottom border-secondary-subtle pb-2 text-primary">Perfil Público</h5>
                <div class="row g-3 mb-4">
                  
                  <div class="col-12">
                    <label for="nombreCompleto" class="form-label fw-medium">Nombre y Apellido</label>
                    <input type="text" class="form-control bg-body-tertiary" id="nombreCompleto" name="nombre_completo" required>
                  </div>
                  
                  <div class="col-12">
                    <label for="imagenPerfil" class="form-label fw-medium">Imagen Principal</label>
                    <input class="form-control bg-body-tertiary" type="file" id="imagenPerfil" name="imagen_perfil" accept="image/*" required>
                    <div class="form-text">Esta imagen se utilizará cada vez que realices una acción en el portal.</div>
                  </div>

                  <div class="col-md-6">
                    <label for="titulo" class="form-label fw-medium">Título Profesional <span class="text-body-secondary fw-normal">(Opcional)</span></label>
                    <input type="text" class="form-control bg-body-tertiary" id="titulo" name="titulo_profesional" placeholder="Ej: Ingeniero en Sistemas">
                  </div>

                  <div class="col-md-6 d-none" id="comprobanteContainer">
                    <label for="comprobanteTitulo" class="form-label fw-medium text-warning-emphasis">Comprobante del Título</label>
                    <input class="form-control bg-body-tertiary border-warning-subtle" type="file" id="comprobanteTitulo" name="comprobante_titulo" accept=".pdf, .jpg, .png">
                    <div class="form-text">Requerido para validar tu título en la plataforma.</div>
                  </div>

                  <div class="col-12 mt-3">
                    <label for="antecedentes" class="form-label fw-medium">Antecedentes</label>
                    <textarea class="form-control bg-body-tertiary" id="antecedentes" name="antecedentes" rows="3" placeholder="Cuéntanos brevemente sobre tu experiencia o estudios..."></textarea>
                  </div>

                  <div class="col-12">
                    <label for="intereses" class="form-label fw-medium">Intereses (Etiquetas)</label>
                    <input type="text" class="form-control bg-body-tertiary" id="intereses" name="intereses" placeholder="Ej: Programación, Diseño, Frontend...">
                    <div class="form-text">Separa los intereses por comas. Nos ayudará a recomendarte los mejores cursos.</div>
                  </div>

                </div>

                <div class="d-grid mt-5">
                  <button type="submit" class="btn btn-primary btn-lg fw-bold" name="registrar_usuario">Registrarme</button>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Enlazamos nuestro registro.js -->
  <script src="../js/registro.js"></script>

<?php include 'includes/pie.php'; ?>