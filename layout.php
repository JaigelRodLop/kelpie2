<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol = $_SESSION['role'] ?? null;
$nombreUsuario = $_SESSION['user_name'] ?? 'Invitado';

$icono = "bi-person-circle";
if ($rol === ROLE_ADMIN) {
    $icono = "bi-shield-lock";
} elseif ($rol === ROLE_TECNICO) {
    $icono = "bi-wrench";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kelpie Helpdesk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Iconos de Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Kelpie Helpdesk</a>
            <div class="d-flex align-items-center ms-auto">
                <i class="bi <?= $icono ?> text-white fs-4 me-2"></i>
                <span class="navbar-text text-white">
                    <?= htmlspecialchars($nombreUsuario) ?>
                </span>
                <a href="logout.php" class="btn btn-danger btn-sm ms-3">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <!-- Contenido dinámico -->
    <main class="container mt-4">
        <?php
        // Aquí se inyecta el contenido de cada dashboard
        if (isset($contenido)) {
            echo $contenido;
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">Kelpie Helpdesk © <?= date("Y") ?> | Soporte técnico y gestión de tickets</p>
    </footer>

    <!-- JS -->
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>