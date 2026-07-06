<?php
require_once __DIR__ . '/../config/auth.php';

class AuthMiddleware {
    public static function check() {
        if (!isAuthenticated()) {
            header("Location: /kelpie-helpdesk/public/index.php");
            exit;
        }
    }
}
?>