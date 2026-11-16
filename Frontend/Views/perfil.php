<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PROTECCIÓN DE RUTA
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// --- NUEVO: Obtener datos frescos de la BD ---
require_once '../../Backend/Config/database.php';
require_once '../../Backend/Models/Usuario.php';

$db = new Conexion();
$conexion = $db->conectar();
// Traemos toda la fila del usuario desde MySQL
$datosUsuario = Usuario::obtenerPorId($conexion, $_SESSION['usuario_id']);
// ----------------------------------------------

$clase_body = "d-flex flex-column min-vh-100 bg-body-secondary";
include 'includes/encabezado.php'; 
?>

  <main class="flex-grow-1 py-5">
    <div class="container">
      
      <!-- Encabezado del Perfil -->
      <div class="row mb-4 align-items-center bg-body shadow-sm rounded-4 p-4 mx-0 border-0">
        <div class="col-auto">
          <?php if (!empty($datosUsuario['imagen_perfil'])): ?>
            <img src="../../Resources/Uploads/<?php echo htmlspecialchars($datosUsuario['imagen_perfil']); ?>" alt="Avatar" width="100" height="100" class="rounded-circle object-fit-cover border border-3 border-primary shadow-sm">
          <?php else: ?>
            <i class="bi bi-person-circle text-secondary" style="font-size: 5rem;"></i>
          <?php endif; ?>
        </div>
        <div class="col">
          <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($datosUsuario['nombre_completo']); ?></h2>
          
          <!-- Lógica dinámica para mostrar el Plan real -->
          <?php if($datosUsuario['plan'] == 'pro'): ?>
            <span class="badge bg-warning text-dark shadow-sm mb-2"><i class="bi bi-star-fill me-1"></i>Plan PRO</span>
          <?php elseif($datosUsuario['plan'] == 'admin'): ?>
            <span class="badge bg-danger shadow-sm mb-2"><i class="bi bi-shield-lock-fill me-1"></i>Administrador</span>
          <?php else: ?>
            <span class="badge bg-secondary shadow-sm mb-2">Plan Gratuito</span>
          <?php endif; ?>

          <!-- Mostramos el email real traído de la base de datos -->
          <p class="text-muted mb-0"><i class="bi bi-envelope-fill me-2"></i><?php echo htmlspecialchars($datosUsuario['email']); ?></p>
        </div>
      </div>

      <div class="row">
        <!-- Menú lateral (Pestañas) -->
        <div class="col-md-4 col-lg-3 mb-4">
          <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-3">
              <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <button class="nav-link active text-start fw-medium mb-1" id="v-pills-datos-tab" data-bs-toggle="pill" data-bs-target="#v-pills-datos" type="button" role="tab" aria-controls="v-pills-datos" aria-selected="true">
                  <i class="bi bi-person-lines-fill me-2"></i>Datos Personales
                </button>
                <button class="nav-link text-start fw-medium mb-1" id="v-pills-seguridad-tab" data-bs-toggle="pill" data-bs-target="#v-pills-seguridad" type="button" role="tab" aria-controls="v-pills-seguridad" aria-selected="false">
                  <i class="bi bi-shield-lock-fill me-2"></i>Seguridad
                </button>
                <button class="nav-link text-start fw-medium" id="v-pills-pro-tab" data-bs-toggle="pill" data-bs-target="#v-pills-pro" type="button" role="tab" aria-controls="v-pills-pro" aria-selected="false">
                  <i class="bi bi-star-fill text-warning me-2"></i>Suscripción PRO
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Contenido de las Pestañas -->
        <div class="col-md-8 col-lg-9">
          <div class="tab-content" id="v-pills-tabContent">
            
            <!-- PESTAÑA 1: DATOS PERSONALES -->
            <div class="tab-pane fade show active" id="v-pills-datos" role="tabpanel" aria-labelledby="v-pills-datos-tab" tabindex="0">
              <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                  <h4 class="fw-bold mb-4 border-bottom border-secondary-subtle pb-2">Información del Perfil</h4>
                  
                  <form action="../../Backend/Controllers/PerfilController.php" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label fw-medium">Nombre y Apellido</label>
                        <input type="text" class="form-control bg-body-tertiary" name="nombre_completo" value="<?php echo htmlspecialchars($_SESSION['nombre_completo']); ?>" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-medium" for="titulo">Título Profesional</label>
                        <input type="text" class="form-control bg-body-tertiary" id="titulo" name="titulo_profesional" value="<?php echo htmlspecialchars($datosUsuario['titulo_profesional'] ?? ''); ?>">
                      </div>
                      
                      <!-- Contenedor del comprobante oculto por defecto -->
                      <div class="col-md-6 d-none" id="comprobanteContainer">
                        <label for="comprobanteTitulo" class="form-label fw-medium text-warning-emphasis">Actualizar Comprobante (Opcional)</label>
                        <input class="form-control bg-body-tertiary border-warning-subtle" type="file" id="comprobanteTitulo" name="comprobante_titulo" accept=".pdf, .jpg, .png">
                        <div class="form-text">Sube un nuevo archivo solo si cambiaste tu título.</div>
                      </div>
                      
                      <div class="col-12 mt-3">
                        <label class="form-label fw-medium">Actualizar Imagen Principal</label>
                        <input class="form-control bg-body-tertiary" type="file" name="nueva_imagen" accept="image/*">
                      </div>

                      <div class="col-12 mt-3">
                        <label class="form-label fw-medium">Antecedentes</label>
                        <textarea class="form-control bg-body-tertiary" name="antecedentes" rows="3"><?php echo htmlspecialchars($datosUsuario['antecedentes'] ?? ''); ?></textarea>
                      </div>

                      <div class="col-12">
                        <label class="form-label fw-medium">Intereses (Etiquetas)</label>
                        <input type="text" class="form-control bg-body-tertiary" name="intereses" placeholder="Ej: Python, Roblox Studio, Arduino, Desarrollo Web">
                        <div class="form-text">Separa los intereses por comas para mejorar tus recomendaciones de cursos.</div>
                      </div>

                      <div class="col-12 mt-4 text-end">
                        <button type="submit" name="actualizar_perfil" class="btn btn-primary fw-medium">Guardar Cambios</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- PESTAÑA 2: SEGURIDAD (CONTRASEÑA) -->
            <div class="tab-pane fade" id="v-pills-seguridad" role="tabpanel" aria-labelledby="v-pills-seguridad-tab" tabindex="0">
              <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                  <h4 class="fw-bold mb-4 border-bottom border-secondary-subtle pb-2">Cambiar Contraseña</h4>
                  
                  <form action="../../Backend/Controllers/PerfilController.php" method="POST">
                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label fw-medium">Contraseña Actual</label>
                        <input type="password" class="form-control bg-body-tertiary" name="password_actual" required>
                      </div>
                      <div class="col-md-6 mt-3">
                        <label class="form-label fw-medium">Nueva Contraseña</label>
                        <input type="password" class="form-control bg-body-tertiary" name="password_nueva" required>
                      </div>
                      <div class="col-md-6 mt-3">
                        <label class="form-label fw-medium">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control bg-body-tertiary" name="password_confirmacion" required>
                      </div>

                      <div class="col-12 mt-4 text-end">
                        <button type="submit" name="cambiar_password" class="btn btn-primary fw-medium">Actualizar Contraseña</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>


            <!-- PESTAÑA 3: SUSCRIPCIÓN PRO -->
            <div class="tab-pane fade" id="v-pills-pro" role="tabpanel" aria-labelledby="v-pills-pro-tab" tabindex="0">
              <div class="card shadow-sm border-0 rounded-4 mb-4 border-warning border-2">
                <div class="card-body p-4 p-md-5">

                  <?php if ($datosUsuario['plan'] === 'pro'): ?>
                    <!-- CASO 1: YA ES USUARIO PRO -->
                    <div class="text-center py-3">
                      <div class="mb-3">
                        <i class="bi bi-star-fill text-warning" style="font-size: 3.5rem;"></i>
                      </div>
                      <h4 class="fw-bold mb-2">¡Ya tienes el Plan PRO activo!</h4>
                      <p class="text-body-secondary mb-3">
                        Disfrutas de todos los beneficios exclusivos de Cursolandia sin restricciones.
                      </p>
                      
                      <?php if (!empty($datosUsuario['vencimiento_pro'])): ?>
                        <div class="d-inline-block bg-body-tertiary border border-warning-subtle rounded-3 px-4 py-2 mb-4">
                          <small class="text-muted d-block">Vigencia de tu suscripción hasta:</small>
                          <strong class="text-warning-emphasis fs-5">
                            <i class="bi bi-calendar-check me-1"></i><?php echo date("d/m/Y", strtotime($datosUsuario['vencimiento_pro'])); ?>
                          </strong>
                        </div>
                      <?php endif; ?>

                      <ul class="list-group list-group-flush text-start max-w-md mx-auto rounded-3 border">
                        <li class="list-group-item bg-body-tertiary"><i class="bi bi-check-circle-fill text-success me-2"></i>Creación ilimitada de cursos y sin tope de subida de archivos.</li>
                        <li class="list-group-item bg-body-tertiary"><i class="bi bi-check-circle-fill text-success me-2"></i>Tus cursos aparecen en la sección <strong>Destacados</strong> de la portada.</li>
                        <li class="list-group-item bg-body-tertiary"><i class="bi bi-check-circle-fill text-success me-2"></i>Inscripciones simultáneas ilimitadas en otros cursos.</li>
                      </ul>
                    </div>

                  <?php elseif ($datosUsuario['plan'] === 'admin'): ?>
                    <!-- CASO 2: ES ADMINISTRADOR -->
                    <div class="text-center py-4">
                      <div class="mb-3">
                        <i class="bi bi-shield-lock-fill text-danger" style="font-size: 3.5rem;"></i>
                      </div>
                      <h4 class="fw-bold mb-2">Cuenta con Rol de Administrador</h4>
                      <p class="text-body-secondary mb-0">
                        Ya cuentas con acceso total y todos los beneficios de la plataforma habilitados por defecto, por lo que no necesitas suscribirte al Plan PRO.
                      </p>
                    </div>

                  <?php else: ?>
                    <!-- CASO 3: USUARIO GRATUITO (Muestra el formulario) -->
                    <div class="d-flex align-items-center mb-4 border-bottom border-secondary-subtle pb-2">
                      <h4 class="fw-bold mb-0">Solicitar Plan PRO</h4>
                      <span class="badge text-bg-warning ms-3">Beneficios Exclusivos</span>
                    </div>
                    
                    <p class="text-body-secondary mb-4">
                      Como usuario PRO podrás crear cursos sin límite de cantidad, tus cursos aparecerán destacados en la página principal y no tendrás restricción a la hora de anotarte en otros cursos.
                    </p>

                    <div class="bg-body-tertiary p-4 rounded-3 border border-secondary-subtle mb-4">
                      <h6 class="fw-bold">Instrucciones:</h6>
                      <ol class="small text-muted mb-0">
                        <li>Realiza la transferencia del pago acordado por fuera del sistema.</li>
                        <li>Adjunta el comprobante de pago en el formulario debajo.</li>
                        <li>Envía un mensaje al administrador. Tu cuenta será actualizada una vez verificado el pago.</li>
                      </ol>
                    </div>
                    
                    <form action="../../Backend/Controllers/PerfilController.php" method="POST" enctype="multipart/form-data">
                      <div class="row g-3">
                        <div class="col-12">
                          <label class="form-label fw-medium text-primary">Comprobante de Pago (Adjunto)</label>
                          <input class="form-control bg-body-tertiary border-primary-subtle" type="file" name="comprobante_pro" accept=".pdf, .jpg, .png" required>
                        </div>
                        <div class="col-12 mt-3">
                          <label class="form-label fw-medium">Mensaje para el Administrador</label>
                          <textarea class="form-control bg-body-tertiary" name="mensaje_admin" rows="4" placeholder="Indica fecha, monto o detalles de tu transferencia..." required></textarea>
                        </div>

                        <div class="col-12 mt-4 text-end">
                          <button type="submit" name="solicitar_pro" class="btn btn-warning fw-bold text-dark">
                            <i class="bi bi-send-fill me-2"></i>Enviar Solicitud
                          </button>
                        </div>
                      </div>
                    </form>
                  <?php endif; ?>

                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </main>

<!-- Enlazamos nuestro perfil.js -->
  <script src="../js/perfil.js"></script>

<?php include 'includes/pie.php'; ?>