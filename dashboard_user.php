<?php
require_once __DIR__ . '/src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/src/middleware/RoleMiddleware.php';
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/controllers/TicketController.php';
require_once __DIR__ . '/src/controllers/CommentController.php';

AuthMiddleware::check();
RoleMiddleware::requireRole(ROLE_USER);

$db = new Database();
$conn = $db->connect();

$ticketController = new TicketController($conn);
$commentController = new CommentController($conn);

$usuario_id = $_SESSION['user_id'];
$tickets = $ticketController->getByUser($usuario_id);

// Crear ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'], $_POST['descripcion']) && !isset($_POST['comentario'])) {
    $ticketController->create($_POST['titulo'], $_POST['descripcion'], $usuario_id);
    header("Location: dashboard_user.php?success=ticket");
    exit;
}

// Crear comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'], $_POST['comentario'])) {
    $commentController->create($_POST['ticket_id'], $usuario_id, $_POST['comentario']);
    header("Location: dashboard_user.php?success=comentario");
    exit;
}

ob_start();
?>

<h2 class="mb-4">Panel del Usuario</h2>

<!-- Alertas -->
<?php if (isset($_GET['success']) && $_GET['success'] === 'ticket'): ?>
    <div class="alert alert-success">✅ Ticket creado correctamente.</div>
<?php elseif (isset($_GET['success']) && $_GET['success'] === 'comentario'): ?>
    <div class="alert alert-info">💬 Comentario agregado exitosamente.</div>
<?php endif; ?>

<!-- Formulario para crear ticket -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Crear nuevo ticket</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" name="titulo" id="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Crear ticket</button>
        </form>
    </div>
</div>

<!-- Lista de tickets -->
<?php if (empty($tickets)): ?>
    <div class="alert alert-info">No has creado ningún ticket aún.</div>
<?php else: ?>
    <?php foreach ($tickets as $t): ?>
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
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
                <p><strong>Técnico asignado:</strong> <?= $t['tecnico_nombre'] ?? '<span class="text-muted">No asignado</span>' ?></p>

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

<?php
$contenido = ob_get_clean();
include 'layout.php';
