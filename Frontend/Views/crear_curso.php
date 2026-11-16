<?php
session_start();
// si el usuario no está logueado, lo redirigimos al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../../Backend/Config/database.php';
require_once '../../Backend/Models/Curso.php';

$db = new Conexion();
$conexion = $db->conectar();

$es_pro = (isset($_SESSION['plan']) && ($_SESSION['plan'] === 'pro' || $_SESSION['plan'] === 'admin'));
$cursos_creados = Curso::contarPorCreador($conexion, $_SESSION['usuario_id']);
$limite_alcanzado = (!$es_pro && $cursos_creados >= 3);

date_default_timezone_set('America/Argentina/Buenos_Aires');
$hoy = date('Y-m-d');

$clase_body = "d-flex flex-column min-vh-100 bg-body-secondary";
include 'includes/encabezado.php'; 
?>

<main class="flex-grow-1 py-5">
  <div class="container">
    
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h2 class="fw-bold mb-0"><i class="bi bi-journal-plus text-primary me-2"></i>Crear Nuevo Curso</h2>
          <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Volver</a>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-body p-4 p-md-5">
            <?php if (isset($_GET['error']) && $_GET['error'] === 'fechas_invalidas'): ?>
              <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Las fechas no son válidas. El curso no puede iniciar antes de hoy ni finalizar antes de su fecha de inicio.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <?php if ($limite_alcanzado || (isset($_GET['error']) && $_GET['error'] === 'limite_creacion')): ?>
              <div class="alert alert-danger shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Límite de creación alcanzado (3/3).</strong> Con el Plan Gratuito solo puedes crear hasta 3 cursos. 
                <a href="perfil.php" class="alert-link">Solicita el Plan PRO</a> para publicar cursos sin límite.
              </div>
            <?php elseif (!$es_pro): ?>
              <div class="alert alert-info py-2 shadow-sm mb-4">
                <i class="bi bi-info-circle-fill me-2"></i>
                Plan Gratuito: llevas <strong><?php echo $cursos_creados; ?> de 3</strong> cursos creados.
              </div>
            <?php endif; ?>

            <form action="../../Backend/Controllers/CursoController.php" method="POST" enctype="multipart/form-data">
              <fieldset <?php echo $limite_alcanzado ? 'disabled' : ''; ?>>
                
                

              </fieldset>
            </form>
            <form action="../../Backend/Controllers/CursoController.php" method="POST" enctype="multipart/form-data">
              
              <!-- SECCIÓN 1: Información Básica -->
              <h5 class="fw-bold text-primary mb-3 border-bottom border-secondary-subtle pb-2">1. Información Básica</h5>
              <div class="row g-3 mb-4">
                <div class="col-12">
                  <label class="form-label fw-medium">Título del Curso <span class="text-danger">*</span></label>
                  <input type="text" class="form-control bg-body-tertiary" name="titulo" required maxlength="150" placeholder="Ej: Introducción a la Programación con Python">
                </div>
                
                <div class="col-12">
                  <label class="form-label fw-medium">Descripción <span class="text-danger">*</span></label>
                  <textarea class="form-control bg-body-tertiary" name="descripcion" rows="4" required placeholder="Describe qué aprenderán los alumnos..."></textarea>
                </div>

                <div class="col-12">
                  <label class="form-label fw-medium">Etiquetas (Temas)</label>
                  <input type="text" class="form-control bg-body-tertiary" name="etiquetas" placeholder="Ej: Python, Backend, Lógica">
                  <div class="form-text">Sepáralas por comas. Ayudará a los alumnos a encontrar tu curso.</div>
                </div>
              </div>

              <!-- SECCIÓN 2: Modalidad y Fechas -->
              <h5 class="fw-bold text-primary mb-3 border-bottom border-secondary-subtle pb-2">2. Modalidad y Fechas</h5>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label fw-medium" for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                  <input type="date" class="form-control bg-body-tertiary" id="fecha_inicio" name="fecha_inicio" min="<?php echo $hoy; ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium" for="fecha_fin">Fecha de Finalización <span class="text-danger">*</span></label>
                  <input type="date" class="form-control bg-body-tertiary" id="fecha_fin" name="fecha_fin" min="<?php echo $hoy; ?>" required>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-medium">Costo ($)</label>
                  <input type="number" step="0.01" min="0" class="form-control bg-body-tertiary" name="costo" placeholder="0 = Gratuito" value="0">
                </div>
                
                <div class="col-md-4">
                  <label class="form-label fw-medium">Cupo de Alumnos</label>
                  <input type="number" min="1" class="form-control bg-body-tertiary" name="cupo" placeholder="Vacío = Sin límite">
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-medium">Privacidad</label>
                  <select class="form-select bg-body-tertiary" name="estado">
                    <option value="publico" selected>Público (Visible para todos)</option>
                    <option value="privado">Privado (Solo con enlace)</option>
                  </select>
                </div>
              </div>

              <!-- SECCIÓN 3: Material Adjunto -->
              <h5 id="seccion-material" class="fw-bold text-primary mb-3 border-bottom border-secondary-subtle pb-2">3. Material de Estudio</h5>
              <div class="row g-3 mb-4">
                <div class="col-12">
                  <div class="alert <?php echo $es_pro ? 'alert-warning border-warning' : 'alert-secondary border-secondary-subtle'; ?> py-2 shadow-sm">
                    <i class="bi <?php echo $es_pro ? 'bi-star-fill text-warning' : 'bi-info-circle text-secondary'; ?> me-2"></i>
                    <?php if($es_pro): ?>
                      <strong>Plan PRO:</strong> Puedes subir archivos sin restricciones estrictas de peso.
                    <?php else: ?>
                      <strong>Plan Gratuito:</strong> Máximo 5 archivos. Límite de 5MB por archivo y 10MB en total.
                    <?php endif; ?>
                  </div>

                  <!-- MENSAJES DE ERROR DE CARGA DE MATERIAL -->
                  <?php if (isset($_GET['error'])): ?>
                    <?php if ($_GET['error'] === 'limite_archivos'): ?>
                      <p class="text-danger fw-semibold small mb-2">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>No puedes subir más de 5 archivos con el Plan Gratuito.
                      </p>
                    <?php elseif ($_GET['error'] === 'peso_individual'): ?>
                      <p class="text-danger fw-semibold small mb-2">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>Uno de los archivos supera el peso máximo individual permitido.
                      </p>
                    <?php elseif ($_GET['error'] === 'peso_total'): ?>
                      <p class="text-danger fw-semibold small mb-2">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>El peso total de los archivos seleccionados supera el límite de 10 MB.
                      </p>
                    <?php endif; ?>
                  <?php endif; ?>

                  <label class="form-label fw-medium">Seleccionar Archivos (PDF, Word, PPT, JPG)</label>
                  <input class="form-control bg-body-tertiary" type="file" name="material_curso[]" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png">
                </div>
              </div>

              <!-- Botón Guardar -->
              <div class="d-grid mt-5">
                <button type="submit" name="crear_curso" class="btn btn-primary btn-lg fw-bold">
                  <i class="bi bi-cloud-arrow-up-fill me-2"></i>Publicar Curso
                </button>
              </div>

            </form>

          </div>
        </div>

      </div>
    </div>

  </div>
</main>

<?php include 'includes/pie.php'; ?>