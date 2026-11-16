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
              <?php 
                $alumnos = Curso::obtenerAlumnosPorCurso($conexion, $curso['id']);
                $total_inscritos = count($alumnos);
              ?>
              <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 bg-body">
                  <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="badge bg-primary">Mis Creaciones</span>
                      <span class="badge bg-secondary-subtle text-secondary-emphasis border">
                        <i class="bi bi-people-fill me-1"></i>
                        <?php echo $total_inscritos; ?><?php echo $curso['cupo'] ? ' / ' . $curso['cupo'] : ''; ?> inscriptos
                      </span>
                    </div>

                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                    <p class="small text-muted mb-3">
                      <i class="bi bi-eye me-1"></i>Estado: <?php echo ucfirst($curso['estado']); ?>
                    </p>
                    
                    <div class="d-flex gap-2 mb-3">
                      <a href="curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-outline-secondary btn-sm fw-medium flex-grow-1">
                        <i class="bi bi-eye me-1"></i>Ver Curso
                      </a>
                      <a href="editar_curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-outline-primary btn-sm fw-medium flex-grow-1">
                        <i class="bi bi-pencil-square me-1"></i>Editar
                      </a>
                    </div>

                    <!-- LISTA DE ALUMNOS INSCRIPTOS (ABAJO) -->
                    <div class="border-top border-secondary-subtle pt-3 mt-auto">
                      <h6 class="small fw-bold text-body-secondary mb-2">
                        <i class="bi bi-person-lines-fill me-1 text-primary"></i>Alumnos inscriptos (<?php echo $total_inscritos; ?>)
                      </h6>

                      <?php if ($total_inscritos > 0): ?>
                        <ul class="list-group list-group-flush rounded-3 border border-secondary-subtle" style="max-height: 180px; overflow-y: auto;">
                          <?php foreach ($alumnos as $alumno): ?>
                            <li class="list-group-item bg-body-tertiary d-flex align-items-center py-2 px-2">
                              <?php if (!empty($alumno['imagen_perfil'])): ?>
                                <img src="../../Resources/Uploads/<?php echo htmlspecialchars($alumno['imagen_perfil']); ?>" alt="Avatar" width="28" height="28" class="rounded-circle object-fit-cover me-2 flex-shrink-0">
                              <?php else: ?>
                                <i class="bi bi-person-circle fs-5 text-secondary me-2 flex-shrink-0"></i>
                              <?php endif; ?>
                              
                              <div class="overflow-hidden">
                                <div class="small fw-semibold text-truncate"><?php echo htmlspecialchars($alumno['nombre_completo']); ?></div>
                                <div class="text-muted text-truncate" style="font-size: 0.72rem;"><?php echo htmlspecialchars($alumno['email']); ?></div>
                              </div>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      <?php else: ?>
                        <p class="small text-muted fst-italic mb-0">Todavía no hay alumnos inscriptos.</p>
                      <?php endif; ?>
                    </div>

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