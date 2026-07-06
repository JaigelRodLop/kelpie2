<?php
require_once __DIR__ . '/../config/roles.php';

class RoleMiddleware {
    public static function requireRole($role) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] != $role) {
            echo "Acceso denegado: no tienes permisos.";
            exit;
        }
    }
}
?>