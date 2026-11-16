<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: mis-cursos.php");
    exit();
}

require_once '../../Backend/Config/database.php';
require_once '../../Backend/Models/Curso.php';

$db = new Conexion();
$conexion = $db->conectar();

$curso_id = intval($_GET['id']);
$curso = Curso::obtenerPorId($conexion, $curso_id);

// Verificamos que exista y que el usuario sea el educador dueño del curso
if (!$curso || intval($curso['creador_id']) !== intval($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$es_pro = (isset($_SESSION['plan']) && ($_SESSION['plan'] === 'pro' || $_SESSION['plan'] === 'admin'));
$etiquetas_actuales = implode(', ', Curso::obtenerEtiquetas($conexion, $curso_id));
$materiales_actuales = Curso::obtenerMateriales($conexion, $curso_id);

$clase_body = "d-flex flex-column min-vh-100 bg-body-secondary";
include 'includes/encabezado.php'; 
?>

<main class="flex-grow-1 py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">

        <div class="d-flex align-items-center justify-content-between mb-4">
          <h2 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Editar Curso</h2>
          <a href="curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver al Curso
          </a>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-body p-4 p-md-5">

            <?php if (isset($_GET['error']) && $_GET['error'] === 'db'): ?>
              <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Ocurrió un error al guardar los cambios. Inténtalo nuevamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <form action="../../Backend/Controllers/CursoController.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">

              <!-- SECCIÓN 1: Información Básica (Editable) -->
              <h5 class="fw-bold text-primary mb-3 border-bottom border-secondary-subtle pb-2">1. Información Básica</h5>
              <div class="row g-3 mb-4">
                <div class="col-12">
                  <label class="form-label fw-medium">Título del Curso <span class="text-danger">*</span></label>
                  <input type="text" class="form-control bg-body-tertiary" name="titulo" required maxlength="150" value="<?php echo htmlspecialchars($curso['titulo']); ?>">
                </div>

                <div class="col-12">
                  <label class="form-label fw-medium">Descripción <span class="text-danger">*</span></label>
                  <textarea class="form-control bg-body-tertiary" name="descripcion" rows="4" required><?php echo htmlspecialchars($curso['descripcion']); ?></textarea>
                </div>

                <div class="col-12">
                  <label class="form-label fw-medium">Etiquetas (Temas)</label>
                  <input type="text" class="form-control bg-body-tertiary" name="etiquetas" value="<?php echo htmlspecialchars($etiquetas_actuales); ?>" placeholder="Ej: Python, Backend, Lógica">
                  <div class="form-text">Sepáralas por comas.</div>
                </div>
              </div>

              <!-- SECCIÓN 2: Modalidad y Fechas (Bloqueados excepto Privacidad) -->
              <h5 class="fw-bold text-primary mb-3 border-bottom border-secondary-subtle pb-2">2. Modalidad y Fechas</h5>
              <div class="alert alert-secondary py-2 small mb-3">
                <i class="bi bi-lock-fill me-1"></i>Las fechas de inicio y finalización, el costo y el cupo máximo no pueden modificarse una vez publicado el curso.
              </div>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label fw-medium text-muted">Fecha de Inicio</label>
                  <input type="date" class="form-control" value="<?php echo htmlspecialchars($curso['fecha_inicio']); ?>" disabled>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium text-muted">Fecha de Finalización</label>
                  <input type="date" class="form-control" value="<?php echo htmlspecialchars($curso['fecha_fin']); ?>" disabled>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-medium text-muted">Costo ($)</label>
                  <input type="text" class="form-control" value="<?php echo $curso['costo'] == 0 ? 'Gratuito ($0)' : '$' . number_format($curso['costo'], 2, ',', '.'); ?>" disabled>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-medium text-muted">Cupo de Alumnos</label>
                  <input type="text" class="form-control" value="<?php echo $curso['cupo'] ? $curso['cupo'] . ' alumnos' : 'Sin límite'; ?>" disabled>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-medium">Privacidad</label>
                  <select class="form-select bg-body-tertiary" name="estado">
                    <option value="publico" <?php echo ($curso['estado'] === 'publico') ? 'selected' : ''; ?>>Público (Visible para todos)</option>
                    <option value="privado" <?php echo ($curso['estado'] === 'privado') ? 'selected' : ''; ?>>Privado (Solo con enlace)</option>
                  </select>
                </div>
              </div>

              <!-- SECCIÓN 3: Material de Estudio -->
              <h5 id="seccion-material" class="fw-bold text-primary mb-3 border-bottom border-secondary-subtle pb-2">3. Material de Estudio</h5>
              <div class="row g-3 mb-4">
                <div class="col-12">

                  <?php if (count($materiales_actuales) > 0): ?>
                    <label class="form-label fw-medium">Archivos actuales (marca los que quieras eliminar):</label>
                    <div class="list-group mb-3 shadow-sm">
                      <?php foreach ($materiales_actuales as $mat): ?>
                        <label class="list-group-item d-flex justify-content-between align-items-center bg-body-tertiary">
                          <div class="form-check mb-0">
                            <input class="form-check-input border-danger" type="checkbox" name="eliminar_materiales[]" value="<?php echo $mat['id']; ?>">
                            <span class="ms-2">
                              <i class="bi bi-file-earmark-text text-primary me-1"></i>
                              <?php echo htmlspecialchars(substr($mat['nombre_archivo'], 25)); ?>
                            </span>
                          </div>
                          <span class="badge bg-secondary rounded-pill"><?php echo number_format($mat['peso_bytes'] / 1048576, 2); ?> MB</span>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <p class="text-muted small">Este curso aún no tiene archivos adjuntos.</p>
                  <?php endif; ?>

                  <div class="alert <?php echo $es_pro ? 'alert-warning border-warning' : 'alert-secondary border-secondary-subtle'; ?> py-2 shadow-sm">
                    <i class="bi <?php echo $es_pro ? 'bi-star-fill text-warning' : 'bi-info-circle text-secondary'; ?> me-2"></i>
                    <?php if($es_pro): ?>
                      <strong>Plan PRO:</strong> Puedes agregar archivos sin restricciones estrictas de peso.
                    <?php else: ?>
                      <strong>Plan Gratuito:</strong> Máximo 5 archivos en total. Límite de 5MB por archivo y 10MB en total (sumando los que ya conservás).
                    <?php endif; ?>
                  </div>

                  <?php if (isset($_GET['error'])): ?>
                    <?php if ($_GET['error'] === 'limite_archivos'): ?>
                      <p class="text-danger fw-semibold small mb-2">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>Superás el máximo de 5 archivos totales permitidos en el Plan Gratuito.
                      </p>
                    <?php elseif ($_GET['error'] === 'peso_individual'): ?>
                      <p class="text-danger fw-semibold small mb-2">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>Uno de los archivos nuevos supera los 5 MB permitidos.
                      </p>
                    <?php elseif ($_GET['error'] === 'peso_total'): ?>
                      <p class="text-danger fw-semibold small mb-2">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>El peso total entre los archivos existentes y los nuevos supera los 10 MB.
                      </p>
                    <?php endif; ?>
                  <?php endif; ?>

                  <label class="form-label fw-medium">Agregar nuevos archivos (PDF, Word, PPT, JPG)</label>
                  <input class="form-control bg-body-tertiary" type="file" name="material_curso[]" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png">
                </div>
              </div>

              <div class="d-grid mt-5">
                <button type="submit" name="editar_curso" class="btn btn-primary btn-lg fw-bold">
                  <i class="bi bi-save-fill me-2"></i>Guardar Cambios
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