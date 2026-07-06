<?php
require_once __DIR__ . '/src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/src/middleware/RoleMiddleware.php';
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/controllers/TicketController.php';
require_once __DIR__ . '/src/controllers/CommentController.php';

AuthMiddleware::check();
RoleMiddleware::requireRole(ROLE_TECNICO);

$db = new Database();
$conn = $db->connect();

$ticketController = new TicketController($conn);
$commentController = new CommentController($conn);

$tecnico_id = $_SESSION['user_id'];
$tickets = $ticketController->getByTecnico($tecnico_id);

// Actualizar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'], $_POST['estado'])) {
    $ticketController->updateStatus($_POST['ticket_id'], $_POST['estado'], $tecnico_id);
    header("Location: dashboard_tecnico.php?success=estado");
    exit;
}

// Crear comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'], $_POST['comentario'])) {
    $commentController->create($_POST['ticket_id'], $tecnico_id, $_POST['comentario']);
    header("Location: dashboard_tecnico.php?success=comentario");
    exit;
}

ob_start();
?>

<h2 class="mb-4">Panel del Técnico</h2>

<!-- Alertas -->
<?php if (isset($_GET['success']) && $_GET['success'] === 'comentario'): ?>
    <div class="alert alert-info">Comentario agregado exitosamente.</div>
<?php elseif (isset($_GET['success']) && $_GET['success'] === 'estado'): ?>
    <div class="alert alert-success">Actualizado correctamente.</div>
<?php endif; ?>

<!-- Lista de tickets asignados -->
<?php if (empty($tickets)): ?>
    <div class="alert alert-info">No tienes tickets asignados aún.</div>
<?php else: ?>
    <?php foreach ($tickets as $t): ?>
        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                <strong>Ticket #<?= $t['id'] ?> — <?= htmlspecialchars($t['titulo']) ?></strong>
            </div>
            <div class="card-body">
                <p><strong>Descripción:</strong> <?= htmlspecialchars($t['descripcion']) ?></p>
                <p><strong>Prioridad:</strong> 
                    <?= $t['prioridad'] ? '<span class="badge bg-info">'.ucfirst($t['prioridad']).'</span>' : '<span class="badge bg-secondary">Sin asignar</span>' ?>
                </p>
                <p><strong>Estado:</strong> 
                    <?php if ($t['estado'] === 'pendiente'): ?>
                        <span class="badge bg-warning">Pendiente</span>
                    <?php elseif ($t['estado'] === 'en proceso'): ?>
                        <span class="badge bg-info">En proceso</span>
                    <?php else: ?>
                        <span class="badge bg-success">Resuelto</span>
                    <?php endif; ?>
                </p>
                <p><strong>Usuario:</strong> <?= $t['usuario_nombre'] ?></p>
                <p><strong>Técnico asignado:</strong> <?= $t['tecnico_nombre'] ?? '<span class="text-muted">No asignado</span>' ?></p>

                <!-- Formulario para actualizar estado -->
                <?php if ($t['estado'] !== 'resuelto'): ?>
                    <form method="POST" onsubmit="return confirmarResolucion(this)">
                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                        <select name="estado" class="form-select">
                            <option value="pendiente" <?= $t['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="en proceso" <?= $t['estado'] === 'en proceso' ? 'selected' : '' ?>>En proceso</option>
                            <option value="resuelto">Resuelto</option>
                        </select>
                        <button type="submit" class="btn btn-success mt-2">Actualizar estado</button>
                    </form>
                <?php else: ?>
                    <p class="text-muted"><em>Este ticket está completado y no puede editarse.</em></p>
                <?php endif; ?>

                <!-- Comentarios -->
                <h6>Comentarios:</h6>
                <?php $comments = $commentController->getByTicket($t['id']); ?>
                <?php if (empty($comments)): ?>
                    <p class="text-muted">No hay comentarios aún.</p>
                <?php else: ?>
                    <ul class="list-group mb-3">
                        <?php foreach ($comments as $c): ?>
                            <li class="list-group-item">
                                <strong><?= $c['autor_nombre'] ?>:</strong> <?= htmlspecialchars($c['texto']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <!-- Formulario para añadir comentario -->
                <?php if ($t['estado'] !== 'resuelto'): ?>
                    <form method="POST">
                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                        <textarea name="comentario" class="form-control" placeholder="Escribe un comentario..." required></textarea>
                        <button type="submit" class="btn btn-primary mt-2">Agregar comentario</button>
                    </form>
                <?php else: ?>
                    <p class="text-muted"><em>Este ticket está completado y no admite más comentarios.</em></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
function confirmarResolucion(form) {
    const estado = form.querySelector('select[name="estado"]').value;
    if (estado === 'resuelto') {
        return confirm("¿Seguro que deseas marcar este ticket como RESUELTO? Una vez confirmado, no se podrá editar ni comentar.");
    }
    return true;
}
</script>

<?php
$contenido = ob_get_clean();
include 'layout.php';