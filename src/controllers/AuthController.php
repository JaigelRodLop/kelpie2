<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Asegura que la sesión esté activa
        }
    }

    public function login($correo, $password) {
        $user = $this->userModel->getUserByEmail($correo);

        if (!$user) {
            return ["success" => false, "message" => "Usuario no encontrado"];
        }

        if (password_verify($password, $user['contraseña'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['rol_id'];
            $_SESSION['user_name'] = $user['nombre'];       
            return ["success" => true, "message" => "Login correcto", "user" => $user];
        }

        return ["success" => false, "message" => "Contraseña incorrecta"];
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        return ["success" => true, "message" => "Sesión cerrada"];
    }

    public function checkAuth() {
        return isset($_SESSION['user_id']);
    }

    public function getAuthUser() {
        if ($this->checkAuth()) {
            return [
                "id" => $_SESSION['user_id'],
                "role" => $_SESSION['role'],
                "user_name" => $_SESSION['user_name']
            ];
        }
        return null;
    }
}
?>