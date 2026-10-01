<?php 
// No definimos $clase_body porque el index usa el body por defecto
include 'includes/encabezado.php'; 
?>

  
  <main>
    
    <header class="bg-primary text-white py-5 mb-4 shadow-sm">
      <div class="container text-center py-3">
        <h1 class="display-5 fw-bold">Comunidad de Aprendizaje Colaborativo</h1>
        <p class="lead mx-auto col-lg-8">Crea tus propios cursos, capacítate con otros usuarios y participa en foros de debate.</p>
        
        
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
      
      <!-- SECCIÓN: CURSOS DESTACADOS (PLAN PRO) -->
      <section class="mb-5">
        <div class="d-flex align-items-center mb-3">
          <h2 class="h4 mb-0 fw-bold"><i class="bi bi-star-fill text-warning me-2"></i>Cursos Destacados</h2>
          <span class="badge text-bg-warning ms-2">PRO</span>
        </div>

        <div class="row g-4">
          <!-- Tarjeta de Curso PRO (Gratuito) -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 card-course border-warning border-2 shadow-sm bg-body-tertiary">
              <img src="https://placehold.co/600x300/495057/ffffff?text=Web+Full+Stack" class="card-img-top object-fit-cover" alt="Portada del curso" height="160">
              
              <div class="card-body d-flex flex-column">
                <h5 class="card-title fw-bold mt-1">Programación Web Full Stack</h5>
                <p class="card-text text-muted small flex-grow-1">
                  Aprende arquitectura web, bases de datos y desarrollo backend desde cero con buenas prácticas.
                </p>
                <div class="mb-3">
                  <span class="badge bg-secondary-subtle text-secondary-emphasis border">#web</span>
                  <span class="badge bg-secondary-subtle text-secondary-emphasis border">#programacion</span>
                  <span class="badge bg-secondary-subtle text-secondary-emphasis border">#php</span>
                </div>
                
                <div class="d-flex align-items-center mb-3">
                  <img src="https://placehold.co/100x100/adb5bd/ffffff?text=L" class="rounded-circle me-2 object-fit-cover" alt="Avatar del creador" width="32" height="32">
                  <small class="text-body-secondary fw-medium">Prof. Laura Méndez</small>
                </div>
                
                <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted border-top border-secondary-subtle pt-3 mt-auto gap-2">
                  <div class="d-flex gap-3">
                    <span title="Fecha"><i class="bi bi-calendar-event me-1 text-primary"></i>01/09 - 30/11</span>
                    <span title="Cupos"><i class="bi bi-people me-1 text-primary"></i>25</span>
                  </div>
                  <div class="d-flex gap-1">
                    <span class="badge text-bg-warning shadow-sm">PRO</span>
                    <span class="badge bg-success shadow-sm">Gratuito</span>
                  </div>
                </div>
              </div>
              <div class="card-footer bg-transparent border-0 pt-0 pb-3 mt-2">
                <a href="#" class="btn btn-primary w-100 fw-medium"><i class="bi bi-eye-fill me-1"></i> Ver Detalle</a>
              </div>
            </div>
          </div>

          <!-- Tarjeta de Curso PRO (Pago) -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 card-course border-warning border-2 shadow-sm bg-body-tertiary">
              <img src="https://placehold.co/600x300/495057/ffffff?text=Bases+de+Datos" class="card-img-top object-fit-cover" alt="Portada del curso" height="160">
              
              <div class="card-body d-flex flex-column">
                <h5 class="card-title fw-bold mt-1">Bases de Datos Relacionales y SQL</h5>
                <p class="card-text text-muted small flex-grow-1">
                  Modelado relacional, normalización (3FN), integridad referencial y consultas avanzadas en MySQL.
                </p>
                <div class="mb-3">
                  <span class="badge bg-secondary-subtle text-secondary-emphasis border">#sql</span>
                  <span class="badge bg-secondary-subtle text-secondary-emphasis border">#mysql</span>
                  <span class="badge bg-secondary-subtle text-secondary-emphasis border">#database</span>
                </div>
                
                <div class="d-flex align-items-center mb-3">
                  <img src="https://placehold.co/100x100/adb5bd/ffffff?text=D" class="rounded-circle me-2 object-fit-cover" alt="Avatar del creador" width="32" height="32">
                  <small class="text-body-secondary fw-medium">Ing. Diego Torres</small>
                </div>
                
                <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted border-top border-secondary-subtle pt-3 mt-auto gap-2">
                  <div class="d-flex gap-3">
                    <span title="Fecha"><i class="bi bi-calendar-event me-1 text-primary"></i>15/09 - 15/12</span>
                    <span title="Cupos"><i class="bi bi-people me-1 text-primary"></i>15</span>
                  </div>
                  <div class="d-flex gap-1">
                    <span class="badge text-bg-warning shadow-sm">PRO</span>
                    <span class="badge bg-primary shadow-sm">$ 15.000</span>
                  </div>
                </div>
              </div>
              <div class="card-footer bg-transparent border-0 pt-0 pb-3 mt-2">
                <a href="#" class="btn btn-primary w-100 fw-medium"><i class="bi bi-eye-fill me-1"></i> Ver Detalle</a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- SECCIÓN: TODOS LOS CURSOS -->
      <section>
        <h2 class="h4 mb-3 fw-bold">Todos los Cursos</h2>
        <div class="row g-4">
          <!-- Tarjeta Regular -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 card-course shadow-sm border border-secondary-subtle bg-body-tertiary">
              <img src="https://placehold.co/600x300/495057/ffffff?text=L%C3%B3gica+de+Programaci%C3%B3n" class="card-img-top object-fit-cover" alt="Portada del curso" height="160">
              
              <div class="card-body d-flex flex-column">
                <h5 class="card-title fw-bold mt-1">Introducción a la Lógica de Programación</h5>
                <p class="card-text text-muted small flex-grow-1">
                  Conceptos iniciales, algoritmos, diagramas de flujo y resolución de problemas estructurados paso a paso.
                </p>
                <div class="mb-3">
                  <span class="badge bg-secondary-subtle text-secondary-emphasis border">#logica</span>
                  <span class="badge bg-secondary-subtle text-secondary-emphasis border">#algoritmos</span>
                </div>
                
                <div class="d-flex align-items-center mb-3">
                  <img src="https://placehold.co/100x100/adb5bd/ffffff?text=M" class="rounded-circle me-2 object-fit-cover" alt="Avatar del creador" width="32" height="32">
                  <small class="text-body-secondary fw-medium">Prof. Martín Gómez</small>
                </div>
                
                <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted border-top border-secondary-subtle pt-3 mt-auto gap-2">
                  <div class="d-flex gap-3">
                    <span title="Fecha"><i class="bi bi-calendar-event me-1 text-primary"></i>01/10 - 01/11</span>
                    <span title="Cupos"><i class="bi bi-people me-1 text-primary"></i>Sin límite</span>
                  </div>
                  <div class="d-flex gap-1">
                    <span class="badge text-bg-secondary shadow-sm">Público</span>
                    <span class="badge bg-success shadow-sm">Gratuito</span>
                  </div>
                </div>
              </div>
              <div class="card-footer bg-transparent border-0 pt-0 pb-3 mt-2">
                <a href="#" class="btn btn-primary w-100 fw-medium"><i class="bi bi-eye-fill me-1"></i> Ver Detalle</a>
              </div>
            </div>
          </div>
        </div>
      </section>
      
    </div>
  </main>

<?php include 'includes/pie.php'; ?>