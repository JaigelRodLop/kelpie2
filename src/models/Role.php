<?php
class Role {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getRoleById($id) {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAllRoles() {
        $stmt = $this->db->query("SELECT * FROM roles");
        return $stmt->fetchAll();
    }

    public function assignRole($user_id, $rol_id) {
        $stmt = $this->db->prepare("UPDATE usuarios SET rol_id=? WHERE id=?");
        return $stmt->execute([$rol_id, $user_id]);
    }
}
?>