<?php
class User {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function createUser($nombre, $correo, $password, $rol_id) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, correo, contraseña, rol_id) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nombre, $correo, $hash, $rol_id]);
    }

    public function getUserByEmail($correo) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE correo=?");
        $stmt->execute([$correo]);
        return $stmt->fetch();
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAllUsers() {
        $stmt = $this->db->query("SELECT * FROM usuarios");
        return $stmt->fetchAll();
    }

    public function updateUser($id, $nombre, $correo, $rol_id) {
        $stmt = $this->db->prepare("UPDATE usuarios SET nombre=?, correo=?, rol_id=? WHERE id=?");
        return $stmt->execute([$nombre, $correo, $rol_id, $id]);
    }

    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function changePassword($id, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE usuarios SET contraseña=? WHERE id=?");
        return $stmt->execute([$hash, $id]);
    }

    public function emailExists($correo) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE correo=?");
        $stmt->execute([$correo]);
        return $stmt->fetchColumn() > 0;
    }
}
?>