<?php
session_start();
// PROTECCIÓN ESTRICTA: Solo administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['plan'] !== 'admin') {
    header("Location: index.php");
    exit();
}

require_once '../../Backend/Config/database.php';
$db = new Conexion();
$conexion = $db->conectar();

// Consultamos solicitudes pendientes con INNER JOIN a usuarios
$sql = "SELECT s.id as solicitud_id, s.mensaje, s.comprobante_pago, s.fecha_solicitud, 
               u.id as usuario_id, u.nombre_completo, u.email 
        FROM solicitudes_pro s 
        INNER JOIN usuarios u ON s.usuario_id = u.id 
        WHERE s.estado = 'pendiente' 
        ORDER BY s.fecha_solicitud DESC";
$resultado = $conexion->query($sql);

// Traer historial de solicitudes resueltas (aprobadas y rechazadas)
$sql_historial = "SELECT s.*, u.nombre_completo, u.email, u.imagen_perfil, u.plan, u.vencimiento_pro 
                  FROM solicitudes_pro s 
                  INNER JOIN usuarios u ON s.usuario_id = u.id 
                  WHERE s.estado != 'pendiente' 
                  ORDER BY s.id DESC";
$res_historial = $conexion->query($sql_historial);

date_default_timezone_set('America/Argentina/Buenos_Aires');
$hoy = date('Y-m-d');

$clase_body = "d-flex flex-column min-vh-100 bg-body-secondary";
include 'includes/encabezado.php'; 
?>

<main class="flex-grow-1 py-5">
  <div class="container">
    <div class="d-flex align-items-center mb-4">
      <h2 class="fw-bold mb-0"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Panel de Administración</h2>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-4">Solicitudes de Plan PRO Pendientes</h5>
        
        <?php if ($resultado && $resultado->num_rows > 0): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-dark">
                <tr>
                  <th>Fecha</th>
                  <th>Usuario</th>
                  <th>Mensaje</th>
                  <th>Comprobante</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php while($fila = $resultado->fetch_assoc()): ?>
                <tr>
                  <td><?php echo date("d/m/Y H:i", strtotime($fila['fecha_solicitud'])); ?></td>
                  <td>
                    <strong><?php echo htmlspecialchars($fila['nombre_completo']); ?></strong><br>
                    <small class="text-muted"><?php echo htmlspecialchars($fila['email']); ?></small>
                  </td>
                  <td><?php echo htmlspecialchars($fila['mensaje']); ?></td>
                  <td>
                    <a href="../../Resources/Uploads/<?php echo htmlspecialchars($fila['comprobante_pago']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-file-earmark-text me-1"></i>Ver Adjunto
                    </a>
                  </td>
                  <td>
                    <!-- Formulario para Aprobar/Rechazar -->
                    <form action="../../Backend/Controllers/AdminController.php" method="POST" class="d-flex gap-2">
                      <input type="hidden" name="solicitud_id" value="<?php echo $fila['solicitud_id']; ?>">
                      <input type="hidden" name="usuario_id" value="<?php echo $fila['usuario_id']; ?>">
                      
                      <button type="submit" name="accion" value="aprobar" class="btn btn-sm btn-success shadow-sm" title="Aprobar PRO">
                        <i class="bi bi-check-lg"></i>
                      </button>
                      <button type="submit" name="accion" value="rechazar" class="btn btn-sm btn-danger shadow-sm" title="Rechazar">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info border-0 shadow-sm"><i class="bi bi-info-circle me-2"></i>No hay solicitudes pendientes por revisar.</div>
        <?php endif; ?>

      </div>
    </div>

    <!-- HISTORIAL DE SOLICITUDES (ACEPTADAS Y RECHAZADAS) -->
    <div class="card shadow-sm border-0 rounded-4 bg-body mt-5">
      <div class="card-body p-4">
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 border-bottom border-secondary-subtle pb-3 gap-2">
          <div>
            <h4 class="fw-bold mb-1">
              <i class="bi bi-clock-history text-primary me-2"></i>Historial de Solicitudes PRO
            </h4>
            <p class="text-muted small mb-0">Registro de usuarios aprobados (con su fecha de vencimiento) y solicitudes rechazadas.</p>
          </div>
          <span class="badge bg-secondary-subtle text-secondary-emphasis border px-3 py-2">
            Total resueltas: <?php echo ($res_historial) ? $res_historial->num_rows : 0; ?>
          </span>
        </div>

        <?php if ($res_historial && $res_historial->num_rows > 0): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th>Usuario</th>
                  <th>Resolución</th>
                  <th>Vencimiento Plan PRO</th>
                  <th>Mensaje / Motivo</th>
                  <th class="text-center">Comprobante</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($hist = $res_historial->fetch_assoc()): ?>
                  <?php
                    $es_aprobada = ($hist['estado'] === 'aprobada' || $hist['estado'] === 'aceptada');
                    $comprobante = $hist['comprobante'] ?? ($hist['comprobante_pago'] ?? ($hist['comprobante_pro'] ?? ''));
                    $mensaje = $hist['mensaje'] ?? ($hist['mensaje_admin'] ?? '');
                    $fecha_sol = $hist['fecha_solicitud'] ?? ($hist['fecha'] ?? null);
                  ?>
                  <tr>
                    <!-- Datos del Usuario -->
                    <td>
                      <div class="d-flex align-items-center">
                        <?php if (!empty($hist['imagen_perfil'])): ?>
                          <img src="../../Resources/Uploads/<?php echo htmlspecialchars($hist['imagen_perfil']); ?>" alt="Avatar" width="38" height="38" class="rounded-circle object-fit-cover me-2 flex-shrink-0">
                        <?php else: ?>
                          <i class="bi bi-person-circle fs-3 text-secondary me-2 flex-shrink-0"></i>
                        <?php endif; ?>
                        <div>
                          <div class="fw-bold"><?php echo htmlspecialchars($hist['nombre_completo']); ?></div>
                          <div class="small text-muted"><?php echo htmlspecialchars($hist['email']); ?></div>
                          <?php if ($fecha_sol): ?>
                            <div class="text-muted" style="font-size: 0.72rem;">
                              Solicitado el <?php echo date("d/m/Y", strtotime($fecha_sol)); ?>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>

                    <!-- Estado de la Resolución -->
                    <td>
                      <?php if ($es_aprobada): ?>
                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-2 py-1">
                          <i class="bi bi-check-circle-fill me-1"></i>Aceptada
                        </span>
                      <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-2 py-1">
                          <i class="bi bi-x-circle-fill me-1"></i>Rechazada
                        </span>
                      <?php endif; ?>
                    </td>

                    <!-- Vencimiento PRO (solo si fue aceptada) -->
                    <td>
                      <?php if ($es_aprobada): ?>
                        <?php if (!empty($hist['vencimiento_pro'])): ?>
                          <?php $esta_vencido = ($hist['vencimiento_pro'] < $hoy); ?>
                          <div class="fw-semibold">
                            <i class="bi bi-calendar-event text-primary me-1"></i>
                            <?php echo date("d/m/Y", strtotime($hist['vencimiento_pro'])); ?>
                          </div>
                          <?php if ($esta_vencido): ?>
                            <span class="badge text-bg-danger" style="font-size: 0.65rem;">Vencido</span>
                          <?php else: ?>
                            <span class="badge text-bg-warning" style="font-size: 0.65rem;">Vigente</span>
                          <?php endif; ?>
                        <?php else: ?>
                          <span class="small text-muted">Sin fecha asignada</span>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-muted small">—</span>
                      <?php endif; ?>
                    </td>

                    <!-- Mensaje de la solicitud -->
                    <td style="max-width: 260px;">
                      <p class="small text-body-secondary mb-0 text-truncate" title="<?php echo htmlspecialchars($mensaje); ?>">
                        <?php echo !empty($mensaje) ? htmlspecialchars($mensaje) : '<span class="fst-italic text-muted">Sin mensaje</span>'; ?>
                      </p>
                    </td>

                    <!-- Comprobante adjunto -->
                    <td class="text-center">
                      <?php if (!empty($comprobante)): ?>
                        <a href="../../Resources/Uploads/<?php echo htmlspecialchars($comprobante); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                          <i class="bi bi-file-earmark-text me-1"></i>Ver
                        </a>
                      <?php else: ?>
                        <span class="text-muted small">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-4 text-muted">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            Todavía no hay solicitudes aprobadas ni rechazadas en el historial.
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</main>

<?php include 'includes/pie.php'; ?>