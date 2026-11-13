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
  </div>
</main>

<?php include 'includes/pie.php'; ?>