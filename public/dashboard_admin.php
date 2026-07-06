<?php
require_once __DIR__ . '/src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/src/middleware/RoleMiddleware.php';
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/../src/controllers/UserController.php';
require_once __DIR__ . '/../src/controllers/TicketController.php';

AuthMiddleware::check();
RoleMiddleware::requireRole(ROLE_ADMIN);

$db = new Database();
$conn = $db->connect();

$userController = new UserController($conn);
$ticketController = new TicketController($conn);

$section = $_GET['section'] ?? 'metrics';

$usuarios = [];
$tickets = [];
$totalTickets = $pendientes = $enProceso = $resueltos = 0;

switch ($section) {
    case 'metrics':
        $tickets = $ticketController->index();
        $totalTickets = count($tickets);
        $pendientes = count(array_filter($tickets, fn($t) => $t['estado'] === 'pendiente'));
        $enProceso = count(array_filter($tickets, fn($t) => $t['estado'] === 'en proceso'));
        $resueltos = count(array_filter($tickets, fn($t) => $t['estado'] === 'resuelto'));
        break;

    case 'tickets':
        $usuarios = $userController->index();

        $filtroUsuario = $_GET['usuario_id'] ?? null;
        $filtroTecnico = $_GET['tecnico_id'] ?? null;
        $filtroEstado  = $_GET['estado'] ?? null;

        if ($filtroUsuario) {
            $tickets = $ticketController->getByUser($filtroUsuario);
        } elseif ($filtroTecnico) {
            $tickets = $ticketController->getByTecnico($filtroTecnico);
        } else {
            $tickets = $ticketController->index();
        }

        if ($filtroEstado) {
            $tickets = array_filter($tickets, fn($t) => $t['estado'] === $filtroEstado);
        }
        break;

    case 'users':
        $usuarios = $userController->index();
        break;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($section === 'users') {
        if ($action === 'create') {
            $result = $userController->create($_POST['nombre'], $_POST['correo'], $_POST['password'], $_POST['rol_id']);
            echo "<div class='alert alert-info'>{$result['message']}</div>";
        }
        if ($action === 'update') {
            $result = $userController->update($_POST['id'], $_POST['nombre'], $_POST['correo'], $_POST['rol_id']);
            echo "<div class='alert alert-warning'>{$result['message']}</div>";
        }
        if ($action === 'delete') {
            $result = $userController->delete($_POST['id']);
            echo "<div class='alert alert-danger'>{$result['message']}</div>";
        }
        if ($action === 'password') {
            $result = $userController->changePassword($_POST['id'], $_POST['newPassword']);
            echo "<div class='alert alert-success'>{$result['message']}</div>";
        }
        $usuarios = $userController->index();
    }

    if ($section === 'tickets' && $action === 'assign') {
        $ticketController->updateStatus($_POST['ticket_id'], 'pendiente', $_POST['tecnico_id']);
        echo "<div class='alert alert-success'>Técnico asignado correctamente al ticket #{$_POST['ticket_id']}.</div>";
        $tickets = $ticketController->index();
    }
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <main class="container">
        <?php
        switch ($section) {
            case 'metrics':
        ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Métricas y Estadísticas</h3>
                    <div>
                        <a href="?section=metrics" class="btn btn-primary me-2">📊 Métricas</a>
                        <a href="?section=tickets" class="btn btn-secondary me-2">🎫 Tickets</a>
                        <a href="?section=users" class="btn btn-secondary">👥 Usuarios</a>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5>Total Tickets</h5>
                                <p class="display-6 text-primary"><?= $totalTickets ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5>Pendientes</h5>
                                <p class="display-6 text-warning"><?= $pendientes ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5>En Proceso</h5>
                                <p class="display-6 text-info"><?= $enProceso ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5>Resueltos</h5>
                                <p class="display-6 text-success"><?= $resueltos ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-3">Distribución de Tickets</h4>
                        <canvas id="ticketsChart" height="120"></canvas>
                    </div>
                </div>

                <script>
                    const ctx = document.getElementById('ticketsChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Pendientes', 'En Proceso', 'Resueltos'],
                            datasets: [{
                                data: [<?= $pendientes ?>, <?= $enProceso ?>, <?= $resueltos ?>],
                                backgroundColor: ['#ffc107','#17a2b8','#28a745']
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'bottom' },
                                title: { display: true, text: 'Estado actual de los tickets' }
                            }
                        }
                    });
                </script>
        <?php
                break;
                case 'tickets':
            ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Gestión de Tickets</h3>
                        <div>
                            <a href="?section=metrics" class="btn btn-secondary me-2">📊 Métricas</a>
                            <a href="?section=tickets" class="btn btn-primary me-2">🎫 Tickets</a>
                            <a href="?section=users" class="btn btn-secondary">👥 Usuarios</a>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <form method="GET" class="row g-3 mb-4">
                        <input type="hidden" name="section" value="tickets">

                        <!-- Filtro por usuario -->
                        <div class="col-md-3">
                            <label class="form-label">Usuario</label>
                            <select name="usuario_id" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($_GET['usuario_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtro por técnico -->
                        <div class="col-md-3">
                            <label class="form-label">Técnico</label>
                            <select name="tecnico_id" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <?php if ($u['rol_id'] == 2): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($_GET['tecnico_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($u['nombre']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtro por estado -->
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendiente" <?= ($_GET['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="en proceso" <?= ($_GET['estado'] ?? '') === 'en proceso' ? 'selected' : '' ?>>En Proceso</option>
                                <option value="resuelto" <?= ($_GET['estado'] ?? '') === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">Filtrar</button>
                        </div>
                    </form>

                    <!-- Lista de tickets -->
                    <?php if (empty($tickets)): ?>
                        <div class="alert alert-info">No se encontraron tickets con los filtros aplicados.</div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($tickets as $t): ?>
                                <div class="col-md-6">
                                    <div class="card mb-3 shadow-sm">
                                        <div class="card-header bg-dark text-white">
                                            <strong>Ticket #<?= $t['id'] ?> — <?= htmlspecialchars($t['titulo']) ?></strong>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Descripción:</strong> <?= htmlspecialchars($t['descripcion']) ?></p>
                                            <p><strong>Estado:</strong> 
                                                <?php if ($t['estado'] === 'pendiente'): ?>
                                                    <span class="badge bg-warning">Pendiente</span>
                                                <?php elseif ($t['estado'] === 'en proceso'): ?>
                                                    <span class="badge bg-info">En Proceso</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Resuelto</span>
                                                <?php endif; ?>
                                            </p>
                                            <p><strong>Usuario:</strong> <?= htmlspecialchars($t['usuario_nombre']) ?></p>
                                            <p><strong>Técnico asignado:</strong> 
                                                <?= $t['tecnico_nombre'] ?? '<span class="text-muted">No asignado</span>' ?>
                                            </p>

                                            <!-- Asignar técnico -->
                                            <?php if ($t['estado'] !== 'resuelto'): ?>
                                                <form method="POST" class="mt-2">
                                                    <input type="hidden" name="action" value="assign">
                                                    <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                                    <div class="input-group">
                                                        <select name="tecnico_id" class="form-select">
                                                            <option value="">Seleccionar técnico</option>
                                                            <?php foreach ($usuarios as $u): ?>
                                                                <?php if ($u['rol_id'] == 2): ?>
                                                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" class="btn btn-primary">Asignar</button>
                                                    </div>
                                                </form>
                                            <?php else: ?>
                                                <p class="text-muted"><em>Este ticket está completado y no puede editarse.</em></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
            <?php
                    break;
                case 'users':
            ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Gestión de Usuarios</h3>
                        <div>
                            <a href="?section=metrics" class="btn btn-secondary me-2">📊 Métricas</a>
                            <a href="?section=tickets" class="btn btn-secondary me-2">🎫 Tickets</a>
                            <a href="?section=users" class="btn btn-primary">👥 Usuarios</a>
                        </div>
                    </div>

                    <!-- Botón para abrir modal de creación -->
                    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
                        ➕ Crear nuevo usuario
                    </button>

                    <!-- Lista de usuarios -->
                    <?php if (empty($usuarios)): ?>
                        <div class="alert alert-info">No hay usuarios registrados aún.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover shadow-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Rol</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $u): ?>
                                        <?php
                                            $rolNombre = match($u['rol_id']) {
                                                1 => 'Admin',
                                                2 => 'Técnico',
                                                3 => 'Usuario',
                                                default => 'Desconocido'
                                            };
                                        ?>
                                        <tr>
                                            <td><?= $u['id'] ?></td>
                                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                                            <td><?= htmlspecialchars($u['correo']) ?></td>
                                            <td><?= $rolNombre ?></td>
                                            <td>
                                                <!-- Botón eliminar -->
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                </form>

                                                <!-- Botón editar -->
                                                <button 
                                                    class="btn btn-warning btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editarUsuarioModal<?= $u['id'] ?>">
                                                    Editar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Modales de edición (fuera de la tabla) -->
                    <?php foreach ($usuarios as $u): ?>
                        <div class="modal fade" id="editarUsuarioModal<?= $u['id'] ?>" tabindex="-1" aria-labelledby="editarUsuarioLabel<?= $u['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <form method="POST" class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editarUsuarioLabel<?= $u['id'] ?>">Editar usuario</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">

                                        <div class="mb-3">
                                            <label class="form-label">Nombre</label>
                                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($u['nombre']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Correo</label>
                                            <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($u['correo']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Rol</label>
                                            <select name="rol_id" class="form-select" required>
                                                <option value="1" <?= $u['rol_id'] == 1 ? 'selected' : '' ?>>Admin</option>
                                                <option value="2" <?= $u['rol_id'] == 2 ? 'selected' : '' ?>>Técnico</option>
                                                <option value="3" <?= $u['rol_id'] == 3 ? 'selected' : '' ?>>Usuario</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-warning">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Modal para crear usuario -->
                    <div class="modal fade" id="crearUsuarioModal" tabindex="-1" aria-labelledby="crearUsuarioLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="POST" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="crearUsuarioLabel">Crear nuevo usuario</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="create">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="nombre" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Correo</label>
                                        <input type="email" name="correo" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Contraseña</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Rol</label>
                                        <select name="rol_id" class="form-select" required>
                                            <option value="1">Admin</option>
                                            <option value="2">Técnico</option>
                                            <option value="3">Usuario</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Crear</button>
                                </div>
                            </form>
                        </div>
                    </div>
            <?php
                    break;
        }
        ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$contenido = ob_get_clean();
include 'layout.php';
