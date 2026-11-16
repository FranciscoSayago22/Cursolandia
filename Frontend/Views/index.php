<?php 
require_once '../../Backend/Config/database.php';
require_once '../../Backend/Models/Curso.php';

$db = new Conexion();
$conexion = $db->conectar();

// Traemos los cursos usando los métodos del modelo
$cursos_destacados = Curso::obtenerDestacados($conexion);
$cursos_regulares = Curso::obtenerRegulares($conexion);

include 'includes/encabezado.php'; 
?>

  <!-- MAIN CONTENT -->
  <main>
    <!-- HERO SECTION -->
    <header class="bg-primary text-white py-5 mb-4 shadow-sm">
      <div class="container text-center py-3">
        <h1 class="display-5 fw-bold">Comunidad de Aprendizaje Colaborativo</h1>
        <p class="lead mx-auto col-lg-8">Crea tus propios cursos, capacítate con otros usuarios y participa en foros de debate.</p>
        
        <!-- BUSCADOR PRINCIPAL -->
        <div class="row justify-content-center mt-4">
          <div class="col-md-8 col-lg-6">
            <form action="#" method="GET" class="input-group">
              <input type="text" class="form-control" name="q" placeholder="Buscar por título o etiqueta (ej. Python, Web, Diseño)..." aria-label="Buscar cursos">
              <button class="btn btn-dark" type="submit">
                <i class="bi bi-search"></i> Buscar
              </button>
            </form>
          </div>
        </div>
      </div>
    </header>

    <div class="container my-4">
      
      <!-- MENSAJES DE ALERTA -->
      <?php if(isset($_GET['exito']) && $_GET['exito'] == 'curso_creado'): ?>
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>¡Curso publicado con éxito!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <!-- SECCIÓN: CURSOS DESTACADOS (PLAN PRO) -->
      <?php if ($cursos_destacados && $cursos_destacados->num_rows > 0): ?>
      <section class="mb-5">
        <div class="d-flex align-items-center mb-3">
          <h2 class="h4 mb-0 fw-bold"><i class="bi bi-star-fill text-warning me-2"></i>Cursos Destacados</h2>
          <span class="badge text-bg-warning ms-2">PRO</span>
        </div>

        <div class="row g-4">
          <?php while($curso = $cursos_destacados->fetch_assoc()): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 card-course border-warning border-2 shadow-sm bg-body-tertiary">
              <!-- Portada Genérica generada con el título -->
              <img src="https://placehold.co/600x300/495057/ffffff?text=<?php echo urlencode(substr($curso['titulo'], 0, 15)); ?>" class="card-img-top object-fit-cover" alt="Portada" height="160">
              
              <div class="card-body d-flex flex-column">
                <h5 class="card-title fw-bold mt-1"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                <p class="card-text text-muted small flex-grow-1">
                  <?php echo htmlspecialchars(substr($curso['descripcion'], 0, 100)) . '...'; ?>
                </p>
                
                <div class="mb-3">
                  <?php 
                  $etiquetas = Curso::obtenerEtiquetas($conexion, $curso['id']);
                  foreach($etiquetas as $tag): 
                  ?>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis border">#<?php echo htmlspecialchars($tag); ?></span>
                  <?php endforeach; ?>
                </div>
                
                <!-- Avatar del Creador -->
                <div class="d-flex align-items-center mb-3">
                  <?php if (!empty($curso['creador_imagen'])): ?>
                    <img src="../../Resources/Uploads/<?php echo htmlspecialchars($curso['creador_imagen']); ?>" class="rounded-circle me-2 object-fit-cover shadow-sm" alt="Avatar" width="32" height="32">
                  <?php else: ?>
                    <i class="bi bi-person-circle fs-4 text-secondary me-2"></i>
                  <?php endif; ?>
                  <small class="text-body-secondary fw-medium"><?php echo htmlspecialchars($curso['creador_nombre']); ?></small>
                </div>
                
                <!-- Info base: Fecha, Cupo y Etiquetas -->
                <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted border-top border-secondary-subtle pt-3 mt-auto gap-2">
                  <div class="d-flex gap-3">
                    <span title="Fecha"><i class="bi bi-calendar-event me-1 text-primary"></i><?php echo date("d/m", strtotime($curso['fecha_inicio'])); ?></span>
                    <span title="Cupos"><i class="bi bi-people me-1 text-primary"></i><?php echo $curso['cupo'] ? $curso['cupo'] : '∞'; ?></span>
                  </div>
                  <div class="d-flex gap-1">
                    <span class="badge text-bg-warning shadow-sm">PRO</span>
                    <?php if($curso['costo'] == 0): ?>
                      <span class="badge bg-success shadow-sm">Gratis</span>
                    <?php else: ?>
                      <span class="badge bg-primary shadow-sm">$<?php echo number_format($curso['costo'], 0, ',', '.'); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              
              <div class="card-footer bg-transparent border-0 pt-0 pb-3 mt-2">
                <a href="curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary w-100 fw-medium">
                  <i class="bi bi-eye-fill me-1"></i> Ver Detalle
                </a>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
      </section>
      <?php endif; ?>

      <!-- SECCIÓN: TODOS LOS CURSOS (REGULARES) -->
      <?php if ($cursos_regulares && $cursos_regulares->num_rows > 0): ?>
      <section>
        <h2 class="h4 mb-3 fw-bold">Cursos Regulares</h2>
        <div class="row g-4">
          <?php while($curso = $cursos_regulares->fetch_assoc()): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 card-course shadow-sm border border-secondary-subtle bg-body-tertiary">
              <img src="https://placehold.co/600x300/495057/ffffff?text=<?php echo urlencode(substr($curso['titulo'], 0, 15)); ?>" class="card-img-top object-fit-cover" alt="Portada" height="160">
              
              <div class="card-body d-flex flex-column">
                <h5 class="card-title fw-bold mt-1"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                <p class="card-text text-muted small flex-grow-1">
                  <?php echo htmlspecialchars(substr($curso['descripcion'], 0, 100)) . '...'; ?>
                </p>
                
                <div class="mb-3">
                  <?php 
                  $etiquetas = Curso::obtenerEtiquetas($conexion, $curso['id']);
                  foreach($etiquetas as $tag): 
                  ?>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis border">#<?php echo htmlspecialchars($tag); ?></span>
                  <?php endforeach; ?>
                </div>
                
                <div class="d-flex align-items-center mb-3">
                  <?php if (!empty($curso['creador_imagen'])): ?>
                    <img src="../../Resources/Uploads/<?php echo htmlspecialchars($curso['creador_imagen']); ?>" class="rounded-circle me-2 object-fit-cover shadow-sm" alt="Avatar" width="32" height="32">
                  <?php else: ?>
                    <i class="bi bi-person-circle fs-4 text-secondary me-2"></i>
                  <?php endif; ?>
                  <small class="text-body-secondary fw-medium"><?php echo htmlspecialchars($curso['creador_nombre']); ?></small>
                </div>
                
                <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted border-top border-secondary-subtle pt-3 mt-auto gap-2">
                  <div class="d-flex gap-3">
                    <span title="Fecha"><i class="bi bi-calendar-event me-1 text-primary"></i><?php echo date("d/m", strtotime($curso['fecha_inicio'])); ?></span>
                    <span title="Cupos"><i class="bi bi-people me-1 text-primary"></i><?php echo $curso['cupo'] ? $curso['cupo'] : '∞'; ?></span>
                  </div>
                  <div class="d-flex gap-1">
                    <?php if($curso['costo'] == 0): ?>
                      <span class="badge bg-success shadow-sm">Gratis</span>
                    <?php else: ?>
                      <span class="badge bg-primary shadow-sm">$<?php echo number_format($curso['costo'], 0, ',', '.'); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              
              <div class="card-footer bg-transparent border-0 pt-0 pb-3 mt-2">
                <a href="curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary w-100 fw-medium">
                  <i class="bi bi-eye-fill me-1"></i> Ver Detalle
                </a>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
      </section>
      <?php else: ?>
        <!-- Mensaje si no hay cursos regulares -->
        <?php if ($cursos_destacados->num_rows == 0): ?>
          <div class="alert alert-info border-0 shadow-sm text-center py-5">
            <i class="bi bi-journal-x fs-1 text-secondary d-block mb-3"></i>
            <h5 class="fw-bold">Aún no hay cursos publicados</h5>
            <p class="mb-0">¡Sé el primero en crear uno y empieza a compartir tu conocimiento!</p>
          </div>
        <?php endif; ?>
      <?php endif; ?>
      
    </div>
  </main>

<?php include 'includes/pie.php'; ?>