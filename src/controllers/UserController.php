<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

class UserController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function create($nombre, $correo, $password, $rol_id) {
        if ($this->userModel->emailExists($correo)) {
            return ["success" => false, "message" => "El correo ya está registrado"];
        }
        $this->userModel->createUser($nombre, $correo, $password, $rol_id);
        return ["success" => true, "message" => "Usuario creado correctamente"];
    }

    public function index() {
        return $this->userModel->getAllUsers();
    }

    public function show($id) {
        return $this->userModel->getUserById($id);
    }

    public function update($id, $nombre, $correo, $rol_id) {
        $this->userModel->updateUser($id, $nombre, $correo, $rol_id);
        return ["success" => true, "message" => "Usuario actualizado correctamente"];
    }

    public function delete($id) {
        $this->userModel->deleteUser($id);
        return ["success" => true, "message" => "Usuario eliminado correctamente"];
    }

    public function changePassword($id, $newPassword) {
        $this->userModel->changePassword($id, $newPassword);
        return ["success" => true, "message" => "Contraseña actualizada correctamente"];
    }
}
?>