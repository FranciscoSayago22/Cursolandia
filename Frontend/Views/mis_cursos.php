<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../../Backend/Config/database.php';
require_once '../../Backend/Models/Curso.php';

$db = new Conexion();
$conexion = $db->conectar();
$usuario_id = $_SESSION['usuario_id'];

// Traemos ambas listas de cursos
$mis_inscripciones = Curso::obtenerInscritos($conexion, $usuario_id);
$mis_creaciones = Curso::obtenerPorCreador($conexion, $usuario_id);

$clase_body = "d-flex flex-column min-vh-100 bg-body-secondary";
include 'includes/encabezado.php'; 
?>

<main class="flex-grow-1 py-5">
  <div class="container">
    
    <div class="d-flex align-items-center mb-4">
      <h2 class="fw-bold mb-0"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Mis Cursos</h2>
    </div>

    <!-- Pestañas de Navegación -->
    <ul class="nav nav-tabs mb-4 border-secondary-subtle" id="misCursosTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold text-dark bg-body-secondary border-secondary-subtle border-bottom-0" id="estudiante-tab" data-bs-toggle="tab" data-bs-target="#estudiante-tab-pane" type="button" role="tab" aria-controls="estudiante-tab-pane" aria-selected="true">
          <i class="bi bi-backpack-fill me-2"></i>Como Estudiante
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold text-secondary" id="educador-tab" data-bs-toggle="tab" data-bs-target="#educador-tab-pane" type="button" role="tab" aria-controls="educador-tab-pane" aria-selected="false">
          <i class="bi bi-easel-fill me-2"></i>Como Educador
        </button>
      </li>
    </ul>

    <div class="tab-content" id="misCursosTabContent">
      
      <!-- PESTAÑA: COMO ESTUDIANTE -->
      <div class="tab-pane fade show active" id="estudiante-tab-pane" role="tabpanel" aria-labelledby="estudiante-tab" tabindex="0">
        <div class="row g-4">
          <?php if ($mis_inscripciones && $mis_inscripciones->num_rows > 0): ?>
            <?php while($curso = $mis_inscripciones->fetch_assoc()): ?>
              <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 bg-body">
                  <div class="card-body">
                    <span class="badge bg-success mb-2">Inscripto</span>
                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                    <p class="small text-muted mb-3"><i class="bi bi-calendar2-check me-1"></i>Inscrito el: <?php echo date("d/m/Y", strtotime($curso['fecha_inscripcion'])); ?></p>
                    
                    <a href="curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-outline-primary w-100 btn-sm fw-medium">
                      <i class="bi bi-box-arrow-in-right me-1"></i>Ir al Curso
                    </a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="col-12 text-center py-5">
              <i class="bi bi-emoji-frown fs-1 text-secondary mb-3 d-block"></i>
              <h5 class="fw-bold">Aún no te has inscrito en ningún curso</h5>
              <a href="index.php" class="btn btn-primary mt-2">Explorar Catálogo</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- PESTAÑA: COMO EDUCADOR -->
      <div class="tab-pane fade" id="educador-tab-pane" role="tabpanel" aria-labelledby="educador-tab" tabindex="0">
        <div class="row g-4">
          <?php if ($mis_creaciones && $mis_creaciones->num_rows > 0): ?>
            <?php while($curso = $mis_creaciones->fetch_assoc()): ?>
              <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 bg-body">
                  <div class="card-body">
                    <span class="badge bg-primary mb-2">Mis Creaciones</span>
                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                    <p class="small text-muted mb-3">
                      <i class="bi bi-people-fill me-1"></i>Estado: <?php echo ucfirst($curso['estado']); ?>
                    </p>
                    
                    <a href="curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-outline-secondary w-100 btn-sm fw-medium">
                      <i class="bi bi-pencil-square me-1"></i>Administrar Curso
                    </a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="col-12 text-center py-5">
              <i class="bi bi-journal-x fs-1 text-secondary mb-3 d-block"></i>
              <h5 class="fw-bold">No has creado ningún curso todavía</h5>
              <a href="crear_curso.php" class="btn btn-primary mt-2">Crear mi primer curso</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>

  </div>
</main>

<!-- Pequeño script para que las pestañas se vean bien al cambiar el tema (claro/oscuro) -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#misCursosTab .nav-link');
    tabs.forEach(tab => {
      tab.addEventListener('click', function() {
        tabs.forEach(t => {
          t.classList.remove('text-dark', 'bg-body-secondary');
          t.classList.add('text-secondary');
        });
        this.classList.remove('text-secondary');
        this.classList.add('text-dark', 'bg-body-secondary');
      });
    });
  });
</script>

<?php include 'includes/pie.php'; ?>