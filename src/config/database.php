<?php
// Dirección oficial de tu BD en Render
$dbUrl = "postgresql://admin:KMyzGAMl8sPRoDe2bL9vqduHX4l7I5Wc@dpg-d95htt1kh4rs738gs48g-a.oregon-postgres.render.com/kelpie_bd_kpj5";

$db = parse_url($dbUrl);

try {
    $pdo = new PDO(
        "pgsql:host={$db['host']};port={$db['port']};dbname=" . ltrim($db['path'], '/'),
        $db['user'],
        $db['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Conexión lista
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
