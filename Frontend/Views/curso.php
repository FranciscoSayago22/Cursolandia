<?php
session_start();
require_once '../../Backend/Config/database.php';

// Si no mandan un ID por URL, los devolvemos al inicio
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$curso_id = intval($_GET['id']);
$db = new Conexion();
$conexion = $db->conectar();

// Consultamos los datos del curso uniendo con la tabla de usuarios para traer el nombre del creador
$sql = "SELECT c.*, u.nombre_completo as creador_nombre, u.imagen_perfil as creador_imagen 
        FROM cursos c 
        INNER JOIN usuarios u ON c.creador_id = u.id 
        WHERE c.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $curso_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    // El curso no existe
    header("Location: index.php?error=no_existe");
    exit();
}
$curso = $resultado->fetch_assoc();
$stmt->close();

$clase_body = "d-flex flex-column min-vh-100 bg-body-secondary";
include 'includes/encabezado.php'; 
?>

<main class="flex-grow-1 py-5">
  <div class="container">
    
    <!-- Alertas (ej: inscripción exitosa) -->
    <?php if(isset($_GET['exito']) && $_GET['exito'] == 'inscrito'): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>¡Inscripción exitosa! Ya puedes acceder al material.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'limite_cursos'): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Límite alcanzado.</strong> Los usuarios del Plan Gratuito solo pueden cursar 3 cursos a la vez. <a href="perfil.php" class="alert-link">Pásate a PRO</a>.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['error']) && $_GET['error'] == 'ya_inscrito'): ?>
    <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-info-circle-fill me-2"></i>Ya te encuentras inscrito en este curso.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Columna Principal: Info del Curso -->
      <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 mb-4">
          <div class="card-body p-4 p-md-5">
            
            <div class="mb-3">
              <?php if($curso['costo'] == 0): ?>
                <span class="badge bg-success shadow-sm fs-6 me-2">Gratuito</span>
              <?php else: ?>
                <span class="badge bg-primary shadow-sm fs-6 me-2">$<?php echo number_format($curso['costo'], 2, ',', '.'); ?></span>
              <?php endif; ?>
              
              <?php if($curso['estado'] == 'privado'): ?>
                <span class="badge text-bg-secondary"><i class="bi bi-lock-fill me-1"></i>Privado</span>
              <?php endif; ?>
            </div>

            <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($curso['titulo']); ?></h1>
            
            <p class="lead text-body-secondary mb-4">
              <?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?>
            </p>

            <h5 class="fw-bold border-bottom border-secondary-subtle pb-2 mb-3">Fechas y Cupo</h5>
            <ul class="list-unstyled mb-4">
              <li class="mb-2"><i class="bi bi-calendar-check text-primary me-2"></i><strong>Inicio:</strong> <?php echo date("d/m/Y", strtotime($curso['fecha_inicio'])); ?></li>
              <li class="mb-2"><i class="bi bi-calendar-x text-primary me-2"></i><strong>Finalización:</strong> <?php echo date("d/m/Y", strtotime($curso['fecha_fin'])); ?></li>
              <li><i class="bi bi-people-fill text-primary me-2"></i><strong>Cupo Máximo:</strong> <?php echo $curso['cupo'] ? $curso['cupo'] . ' alumnos' : 'Sin límite'; ?></li>
            </ul>

            <!-- Módulo 4: Aquí agregaremos el Foro de Preguntas más adelante -->
            <div class="alert alert-light border shadow-sm mt-5 text-center p-4">
              <i class="bi bi-chat-square-text fs-1 text-secondary mb-2"></i>
              <h5 class="fw-bold">Foro de Discusión</h5>
              <p class="text-muted mb-0">Esta sección estará disponible una vez que estés inscrito en el curso.</p>
            </div>

          </div>
        </div>
      </div>

      <!-- Columna Lateral: Creador e Inscripción -->
      <div class="col-lg-4">
        
        <!-- Tarjeta de Inscripción -->
        <div class="card shadow-sm border-0 rounded-4 mb-4 sticky-top" style="top: 100px; z-index: 1;" >
          <div class="card-body p-4 text-center">
            <h4 class="fw-bold mb-4">
              <?php echo $curso['costo'] == 0 ? '¡Curso Gratuito!' : '$' . number_format($curso['costo'], 2, ',', '.'); ?>
            </h4>
            
            <!-- Botón de Acción -->
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <?php if ($_SESSION['usuario_id'] == $curso['creador_id']): ?>
                    <button class="btn btn-secondary w-100 btn-lg fw-bold mb-2" disabled>Eres el Creador</button>
                    <a href="#" class="btn btn-outline-primary w-100">Editar Curso</a>
                <?php else: ?>
                    <form action="../../Backend/Controllers/InscripcionController.php" method="POST">
                        <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                        <button type="submit" name="inscribirse" class="btn btn-primary w-100 btn-lg fw-bold shadow-sm">
                            <i class="bi bi-rocket-takeoff-fill me-2"></i>Inscribirme Ahora
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-primary w-100 btn-lg fw-bold mb-2">Inicia sesión para inscribirte</a>
            <?php endif; ?>
            
            <p class="small text-muted mt-3 mb-0">
              <i class="bi bi-shield-check me-1"></i>Inscripción segura a través de Cursolandia
            </p>
          </div>
        </div>

        <!-- Tarjeta del Creador -->
        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-body p-4 text-center">
            <h6 class="fw-bold text-muted mb-3">Impartido por</h6>
            <?php if (!empty($curso['creador_imagen'])): ?>
              <img src="../../Resources/Uploads/<?php echo htmlspecialchars($curso['creador_imagen']); ?>" class="rounded-circle object-fit-cover mb-2 shadow-sm" width="80" height="80" alt="Creador">
            <?php else: ?>
              <i class="bi bi-person-circle fs-1 text-secondary mb-2"></i>
            <?php endif; ?>
            <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($curso['creador_nombre']); ?></h5>
            <a href="#" class="btn btn-sm btn-light mt-3 border">Ver Perfil</a>
          </div>
        </div>

      </div>
    </div>

  </div>
</main>

<?php include 'includes/pie.php'; ?>