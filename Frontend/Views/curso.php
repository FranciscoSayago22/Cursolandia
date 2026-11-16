<?php
session_start();
require_once '../../Backend/Config/database.php';
require_once '../../Backend/Models/Foro.php';
require_once '../../Backend/Models/Inscripcion.php';

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

$es_creador = (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $curso['creador_id']);
$esta_inscrito = false;

if (isset($_SESSION['usuario_id']) && !$es_creador) {
    $sql_insc = "SELECT id FROM inscripciones WHERE usuario_id = ? AND curso_id = ? AND estado = 'activa'";
    $stmt_insc = $conexion->prepare($sql_insc);
    if ($stmt_insc) {
        $stmt_insc->bind_param("ii", $_SESSION['usuario_id'], $curso_id);
        $stmt_insc->execute();
        $esta_inscrito = ($stmt_insc->get_result()->num_rows > 0);
        $stmt_insc->close();
    }
}

$tiene_acceso = ($es_creador || $esta_inscrito);
$inscritos_actuales = Inscripcion::contarPorCurso($conexion, $curso_id);
$sin_cupo = ($curso['cupo'] !== null && $inscritos_actuales >= intval($curso['cupo']));

$materiales = [];
if ($tiene_acceso) {
    $sql_mat = "SELECT nombre_archivo, peso_bytes FROM material_curso WHERE curso_id = ?";
    $stmt_mat = $conexion->prepare($sql_mat);
    if ($stmt_mat) {
        $stmt_mat->bind_param("i", $curso_id);
        $stmt_mat->execute();
        $res_mat = $stmt_mat->get_result();
        while($m = $res_mat->fetch_assoc()) {
            $materiales[] = $m;
        }
        $stmt_mat->close();
    }
}

$clase_body = "d-flex flex-column min-vh-100 bg-body-secondary";
include 'includes/encabezado.php'; 
?>

<main class="flex-grow-1 py-5">
  <div class="container">
    
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

    <!-- PARTE B: Alerta de curso sin cupo -->
    <?php if(isset($_GET['error']) && $_GET['error'] == 'sin_cupo'): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-people-fill me-2"></i><strong>Sin cupo disponible.</strong> Este curso ya alcanzó su límite máximo de alumnos.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
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
            <p class="lead text-body-secondary mb-4"><?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?></p>

            <h5 class="fw-bold border-bottom border-secondary-subtle pb-2 mb-3">Fechas y Cupo</h5>
            <ul class="list-unstyled mb-4">
              <li class="mb-2"><i class="bi bi-calendar-check text-primary me-2"></i><strong>Inicio:</strong> <?php echo date("d/m/Y", strtotime($curso['fecha_inicio'])); ?></li>
              <li class="mb-2"><i class="bi bi-calendar-x text-primary me-2"></i><strong>Finalización:</strong> <?php echo date("d/m/Y", strtotime($curso['fecha_fin'])); ?></li>
              <!-- PARTE C (1/2): Detalle de cupos ocupados -->
              <li>
                <i class="bi bi-people-fill text-primary me-2"></i><strong>Cupo Máximo:</strong> 
                <?php if ($curso['cupo']): ?>
                  <?php echo $curso['cupo'] . ' alumnos'; ?>
                  <span class="badge <?php echo $sin_cupo ? 'bg-danger' : 'bg-secondary'; ?> ms-1">
                    <?php echo $sin_cupo ? 'Cupo completo' : $inscritos_actuales . ' / ' . $curso['cupo'] . ' ocupados'; ?>
                  </span>
                <?php else: ?>
                  Sin límite
                <?php endif; ?>
              </li>
            </ul>

            <!-- MÓDULO MATERIAL DE ESTUDIO -->
            <?php if ($tiene_acceso): ?>
              <h5 class="fw-bold border-bottom border-secondary-subtle pb-2 mb-3 mt-5">Material de Estudio</h5>
              <?php if (count($materiales) > 0): ?>
                <div class="list-group mb-4 shadow-sm">
                  <?php foreach($materiales as $mat): ?>
                    <a href="../../Resources/Uploads/<?php echo htmlspecialchars($mat['nombre_archivo']); ?>" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                      <span><i class="bi bi-file-earmark-arrow-down-fill text-primary me-2"></i><?php echo htmlspecialchars(substr($mat['nombre_archivo'], 25)); ?></span>
                      <span class="badge bg-secondary rounded-pill"><?php echo number_format($mat['peso_bytes'] / 1048576, 2); ?> MB</span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="text-muted">El creador aún no ha subido material a este curso.</p>
              <?php endif; ?>

              <!-- FORO DE DISCUSIÓN -->
              <h5 id="foro" class="fw-bold border-bottom border-secondary-subtle pb-2 mb-3 mt-5">Foro de Discusión</h5>
              
              <!-- Formulario Nueva Pregunta -->
              <form action="../../Backend/Controllers/ForoController.php" method="POST" class="mb-4">
                <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                <div class="input-group">
                  <input type="text" name="pregunta" class="form-control bg-body-tertiary" placeholder="Escribe tu consulta aquí..." required>
                  <button class="btn btn-primary" type="submit" name="nueva_pregunta">Preguntar</button>
                </div>
              </form>

              <!-- LISTA DE PREGUNTAS -->
              <?php 
              $preguntas = Foro::obtenerPreguntasPorCurso($conexion, $curso_id);
              if (count($preguntas) > 0): 
              ?>
                <div class="d-flex flex-column gap-3">
                  <?php foreach($preguntas as $p): ?>
                    <div class="card shadow-sm border-secondary-subtle">
                      <div class="card-body">
                        
                        <!-- Header de la Pregunta -->
                        <div class="d-flex align-items-center mb-2">
                          <?php if (!empty($p['imagen_perfil'])): ?>
                            <img src="../../Resources/Uploads/<?php echo htmlspecialchars($p['imagen_perfil']); ?>" class="rounded-circle me-2 object-fit-cover" width="30" height="30">
                          <?php else: ?>
                            <i class="bi bi-person-circle fs-4 text-secondary me-2"></i>
                          <?php endif; ?>
                          <strong class="me-2"><?php echo htmlspecialchars($p['nombre_completo']); ?></strong>
                          <?php if($p['plan'] == 'pro'): ?>
                            <span class="badge text-bg-warning me-2" style="font-size: 0.6rem;">PRO</span>
                          <?php endif; ?>
                          <small class="text-muted ms-auto"><?php echo date("d/m/Y H:i", strtotime($p['fecha'])); ?></small>
                        </div>
                        
                        <!-- Texto de la Pregunta -->
                        <p class="mb-3 fw-medium"><?php echo nl2br(htmlspecialchars($p['pregunta'])); ?></p>

                        <!-- MÓDULO DE RESPUESTAS -->
                        <?php 
                        $respuestas = Foro::obtenerRespuestasPorPregunta($conexion, $p['id']);
                        if (count($respuestas) > 0):
                        ?>
                          <div class="bg-body-tertiary p-3 rounded-3 mb-3 border border-secondary-subtle">
                            <?php foreach($respuestas as $r): ?>
                              <div class="d-flex mb-2 pb-2 <?php echo ($r !== end($respuestas)) ? 'border-bottom border-secondary-subtle' : ''; ?>">
                                
                                <div class="me-2">
                                  <?php if (!empty($r['imagen_perfil'])): ?>
                                    <img src="../../Resources/Uploads/<?php echo htmlspecialchars($r['imagen_perfil']); ?>" class="rounded-circle object-fit-cover" width="24" height="24">
                                  <?php else: ?>
                                    <i class="bi bi-person-circle fs-5 text-secondary"></i>
                                  <?php endif; ?>
                                </div>
                                
                                <div class="flex-grow-1">
                                  <div class="d-flex align-items-center">
                                    <strong class="small me-2"><?php echo htmlspecialchars($r['nombre_completo']); ?></strong>
                                    
                                    <!-- Insignia de Creador -->
                                    <?php if($r['usuario_id'] == $curso['creador_id']): ?>
                                      <span class="badge bg-primary me-2" style="font-size: 0.6rem;">Educador</span>
                                    <?php endif; ?>

                                    <!-- Insignia de Respuesta Aceptada -->
                                    <?php if($r['es_aceptada']): ?>
                                      <span class="badge bg-success" style="font-size: 0.6rem;"><i class="bi bi-check-circle-fill me-1"></i>Respuesta Aceptada</span>
                                    <?php endif; ?>
                                  </div>
                                  
                                  <p class="small mb-1"><?php echo nl2br(htmlspecialchars($r['respuesta'])); ?></p>
                                  
                                  <!-- Botón Aceptar Respuesta (Solo Creador del Curso) -->
                                  <?php if($es_creador && !$r['es_aceptada']): ?>
                                    <form action="../../Backend/Controllers/ForoController.php" method="POST" class="d-inline">
                                      <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                                      <input type="hidden" name="pregunta_id" value="<?php echo $p['id']; ?>">
                                      <input type="hidden" name="respuesta_id" value="<?php echo $r['id']; ?>">
                                      <button type="submit" name="marcar_aceptada" class="btn btn-link btn-sm p-0 text-success text-decoration-none" style="font-size: 0.75rem;">
                                        <i class="bi bi-check2-circle me-1"></i>Marcar como útil
                                      </button>
                                    </form>
                                  <?php endif; ?>
                                </div>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>

                        <!-- Formulario para Responder -->
                        <form action="../../Backend/Controllers/ForoController.php" method="POST">
                          <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                          <input type="hidden" name="pregunta_id" value="<?php echo $p['id']; ?>">
                          <div class="input-group input-group-sm">
                            <input type="text" name="respuesta" class="form-control bg-body-tertiary" placeholder="Escribe una respuesta..." required>
                            <button class="btn btn-outline-primary" type="submit" name="nueva_respuesta"><i class="bi bi-send"></i></button>
                          </div>
                        </form>

                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="alert alert-light border text-center text-muted">
                  <i class="bi bi-chat-dots fs-3 d-block mb-2"></i>
                  Aún no hay preguntas. ¡Sé el primero en iniciar un debate!
                </div>
              <?php endif; ?>

            <?php else: ?>
              <div class="alert alert-light border shadow-sm mt-5 text-center p-4">
                <i class="bi bi-lock-fill fs-1 text-secondary mb-2"></i>
                <h5 class="fw-bold">Contenido Bloqueado</h5>
                <p class="text-muted mb-0">Inscríbete en el curso para acceder al material de estudio y participar en el foro.</p>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <!-- Tarjeta de Inscripción -->
        <div class="card shadow-sm border-0 rounded-4 mb-4 sticky-top" style="top: 100px; z-index: 1;">
          <div class="card-body p-4 text-center">
            <h4 class="fw-bold mb-4">
              <?php echo $curso['costo'] == 0 ? '¡Curso Gratuito!' : '$' . number_format($curso['costo'], 2, ',', '.'); ?>
            </h4>
            
            <!-- PARTE C (2/2): Botón bloqueado si no hay cupo -->
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <?php if ($es_creador): ?>
                    <button class="btn btn-secondary w-100 btn-lg fw-bold mb-2" disabled>Eres el Creador</button>
                <?php elseif ($esta_inscrito): ?>
                    <button class="btn btn-success w-100 btn-lg fw-bold mb-2" disabled><i class="bi bi-check-lg me-2"></i>Inscrito</button>
                <?php elseif ($sin_cupo): ?>
                    <button class="btn btn-danger w-100 btn-lg fw-bold mb-2" disabled><i class="bi bi-x-circle me-2"></i>Sin Cupo Disponible</button>
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
            
            <p class="small text-muted mt-3 mb-0"><i class="bi bi-shield-check me-1"></i>Inscripción segura a través de Cursolandia</p>
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
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include 'includes/pie.php'; ?>