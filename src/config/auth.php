<?php
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isAuthenticated() {
    startSession();
    return isset($_SESSION['user_id']);
}

function getAuthUser() {
    startSession();
    if (isset($_SESSION['user_id'])) {
        return [
            "id" => $_SESSION['user_id'],
            "rol_id" => $_SESSION['rol_id'],
            "nombre" => $_SESSION['nombre']
        ];
    }
    return null;
}

function logoutUser() {
    startSession();
    $_SESSION = [];
    session_destroy();
}
?>