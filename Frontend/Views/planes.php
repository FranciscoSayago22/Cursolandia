<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$plan_actual = isset($_SESSION['plan']) ? $_SESSION['plan'] : null;

$clase_body = "d-flex flex-column min-vh-100 bg-body-secondary";
include 'includes/encabezado.php'; 
?>

<main class="flex-grow-1 py-5">
  <div class="container">

    <!-- Encabezado de la sección -->
    <div class="text-center mx-auto mb-5" style="max-width: 700px;">
      <h1 class="fw-bold mb-3">Elegí el plan ideal para aprender y enseñar</h1>
      <p class="lead text-body-secondary mb-0">
        Empezá gratis hoy mismo o pasate a <strong>PRO</strong> para publicar sin límites y destacar tus cursos frente a toda la comunidad.
      </p>
    </div>

    <!-- Tarjetas Comparativas -->
    <div class="row g-4 justify-content-center align-items-stretch mb-5">

      <!-- PLAN GRATUITO -->
      <div class="col-md-6 col-lg-5">
        <div class="card h-100 shadow-sm border border-secondary-subtle rounded-4 bg-body">
          <div class="card-body p-4 p-md-5 d-flex flex-column">
            <div class="mb-3">
              <span class="badge bg-secondary mb-2">Para empezar</span>
              <h3 class="fw-bold mb-1">Plan Gratuito</h3>
              <p class="text-muted small mb-0">Ideal para dar tus primeros pasos como estudiante o educador.</p>
            </div>

            <div class="my-3 pb-3 border-bottom border-secondary-subtle">
              <span class="display-5 fw-bold">$0</span>
              <span class="text-muted"> / siempre</span>
            </div>

            <ul class="list-unstyled d-flex flex-column gap-3 mb-4 flex-grow-1">
              <li>
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                Crear hasta <strong>3 cursos</strong> propios
              </li>
              <li>
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                Inscripción en hasta <strong>3 cursos activos</strong> en simultáneo
              </li>
              <li>
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                Hasta <strong>5 archivos</strong> por curso (máx. 5 MB c/u y <strong>10 MB totales</strong>)
              </li>
              <li>
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                Acceso completo a descarga de materiales y foros de discusión
              </li>
              <li class="text-muted">
                <i class="bi bi-x-circle me-2"></i>
                Sin posicionamiento en Cursos Destacados
              </li>
              <li class="text-muted">
                <i class="bi bi-x-circle me-2"></i>
                Sin insignia distintiva PRO
              </li>
            </ul>

            <!-- Botón de acción dinámico -->
            <div class="mt-auto">
              <?php if (!$plan_actual): ?>
                <a href="registro.php" class="btn btn-outline-primary w-100 btn-lg fw-bold">
                  Crear Cuenta Gratis
                </a>
              <?php elseif ($plan_actual === 'gratis'): ?>
                <button class="btn btn-secondary w-100 btn-lg fw-bold" disabled>
                  <i class="bi bi-check2 me-1"></i>Tu Plan Actual
                </button>
              <?php else: ?>
                <button class="btn btn-outline-secondary w-100 btn-lg fw-bold" disabled>
                  Incluido en tu cuenta
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- PLAN PRO -->
      <div class="col-md-6 col-lg-5">
        <div class="card h-100 shadow border-warning border-2 rounded-4 bg-body position-relative">
          <div class="card-body p-4 p-md-5 d-flex flex-column">
            <div class="mb-3">
              <span class="badge text-bg-warning mb-2"><i class="bi bi-star-fill me-1"></i>Recomendado</span>
              <h3 class="fw-bold mb-1">Plan PRO</h3>
              <p class="text-muted small mb-0">Potenciá tu alcance como educador y cursá sin restricciones.</p>
            </div>

            <div class="my-3 pb-3 border-bottom border-secondary-subtle">
              <span class="display-5 fw-bold text-warning-emphasis">Suscripción Anual</span>
              <span class="text-muted d-block small">Activación manual tras verificación de comprobante</span>
            </div>

            <ul class="list-unstyled d-flex flex-column gap-3 mb-4 flex-grow-1">
              <li>
                <i class="bi bi-star-fill text-warning me-2"></i>
                Creación <strong>ilimitada</strong> de cursos
              </li>
              <li>
                <i class="bi bi-star-fill text-warning me-2"></i>
                Inscripciones simultáneas <strong>ilimitadas</strong> como alumno
              </li>
              <li>
                <i class="bi bi-star-fill text-warning me-2"></i>
                Subida de material de estudio <strong>sin tope de cantidad ni peso</strong>
              </li>
              <li>
                <i class="bi bi-star-fill text-warning me-2"></i>
                Tus cursos aparecen en <strong>Cursos Destacados</strong> en la portada
              </li>
              <li>
                <i class="bi bi-star-fill text-warning me-2"></i>
                Insignia <strong>PRO</strong> visible en tu perfil y en los foros
              </li>
              <li>
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                Acceso completo a foros y gestión de alumnos inscriptos
              </li>
            </ul>

            <!-- Botón de acción dinámico -->
            <div class="mt-auto">
              <?php if (!$plan_actual): ?>
                <a href="login.php" class="btn btn-warning w-100 btn-lg fw-bold text-dark shadow-sm">
                  Iniciá sesión para ser PRO
                </a>
              <?php elseif ($plan_actual === 'gratis'): ?>
                <a href="perfil.php" class="btn btn-warning w-100 btn-lg fw-bold text-dark shadow-sm">
                  <i class="bi bi-rocket-takeoff-fill me-2"></i>Solicitar Plan PRO
                </a>
              <?php elseif ($plan_actual === 'pro'): ?>
                <button class="btn btn-success w-100 btn-lg fw-bold" disabled>
                  <i class="bi bi-star-fill me-2"></i>Ya tenés Plan PRO Activo
                </button>
              <?php elseif ($plan_actual === 'admin'): ?>
                <button class="btn btn-danger w-100 btn-lg fw-bold" disabled>
                  <i class="bi bi-shield-lock-fill me-2"></i>Acceso Total (Administrador)
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Tabla Comparativa Detallada -->
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow-sm border-0 rounded-4 bg-body">
          <div class="card-body p-4">
            <h4 class="fw-bold mb-4 text-center">Comparación rápida de beneficios</h4>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                  <tr>
                    <th>Funcionalidad</th>
                    <th class="text-center">Plan Gratuito</th>
                    <th class="text-center text-warning">Plan PRO</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="fw-medium">Cantidad de cursos creados</td>
                    <td class="text-center">Hasta 3 cursos</td>
                    <td class="text-center fw-bold text-success">Ilimitados</td>
                  </tr>
                  <tr>
                    <td class="fw-medium">Inscripciones simultáneas</td>
                    <td class="text-center">Hasta 3 cursos activos</td>
                    <td class="text-center fw-bold text-success">Ilimitadas</td>
                  </tr>
                  <tr>
                    <td class="fw-medium">Archivos adjuntos por curso</td>
                    <td class="text-center">Máximo 5 archivos</td>
                    <td class="text-center fw-bold text-success">Sin límite</td>
                  </tr>
                  <tr>
                    <td class="fw-medium">Peso máximo de material</td>
                    <td class="text-center">5 MB por archivo / 10 MB total</td>
                    <td class="text-center fw-bold text-success">Sin restricción estricta</td>
                  </tr>
                  <tr>
                    <td class="fw-medium">Visibilidad en la portada</td>
                    <td class="text-center">Listado general</td>
                    <td class="text-center fw-bold"><span class="badge text-bg-warning">Sección Destacados</span></td>
                  </tr>
                  <tr>
                    <td class="fw-medium">Participación en Foros</td>
                    <td class="text-center"><i class="bi bi-check-lg text-success fs-5"></i></td>
                    <td class="text-center"><i class="bi bi-check-lg text-success fs-5"></i> + Insignia PRO</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</main>

<?php include 'includes/pie.php'; ?>