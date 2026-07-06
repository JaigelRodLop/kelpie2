<?php
const ROLE_ADMIN   = 1;
const ROLE_TECNICO = 2;
const ROLE_USER    = 3;

function hasRole($requiredRole) {
    if (!isset($_SESSION['rol_id'])) return false;
    return $_SESSION['rol_id'] == $requiredRole;
}

function isAdmin() {
    return hasRole(ROLE_ADMIN);
}

function isTecnico() {
    return hasRole(ROLE_TECNICO);
}

function isUser() {
    return hasRole(ROLE_USER);
}
?>