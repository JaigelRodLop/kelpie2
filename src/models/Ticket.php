<?php
class Ticket {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function createTicket($titulo, $descripcion, $prioridad, $usuario_id) {
        $stmt = $this->db->prepare("
            INSERT INTO tickets (titulo, descripcion, prioridad, estado, fecha, usuario_id) 
            VALUES (?, ?, ?, 'pendiente', NOW(), ?)
        ");
        return $stmt->execute([$titulo, $descripcion, $prioridad, $usuario_id]);
    }

    public function getTicketById($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.nombre AS usuario_nombre, tec.nombre AS tecnico_nombre
            FROM tickets t
            JOIN usuarios u ON t.usuario_id = u.id
            LEFT JOIN usuarios tec ON t.tecnico_id = tec.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTicketsByUser($usuario_id) {
        $stmt = $this->db->prepare("
            SELECT t.id, t.titulo, t.descripcion, t.estado, t.prioridad,
                   tec.nombre AS tecnico_nombre
            FROM tickets t
            LEFT JOIN usuarios tec ON t.tecnico_id = tec.id
            WHERE t.usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketsByTecnico($tecnico_id) {
        $stmt = $this->db->prepare("
            SELECT t.id, t.titulo, t.descripcion, t.estado, t.prioridad,
                   u.nombre AS usuario_nombre,
                   tec.nombre AS tecnico_nombre
            FROM tickets t
            JOIN usuarios u ON t.usuario_id = u.id
            LEFT JOIN usuarios tec ON t.tecnico_id = tec.id
            WHERE t.tecnico_id = ?
        ");
        $stmt->execute([$tecnico_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTickets() {
        $stmt = $this->db->query("
            SELECT t.*, u.nombre AS usuario_nombre, tec.nombre AS tecnico_nombre
            FROM tickets t
            JOIN usuarios u ON t.usuario_id = u.id
            LEFT JOIN usuarios tec ON t.tecnico_id = tec.id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTicketStatus($id, $estado, $tecnico_id) {
        $stmt = $this->db->prepare("UPDATE tickets SET estado=?, tecnico_id=? WHERE id=?");
        return $stmt->execute([$estado, $tecnico_id, $id]);
    }

    public function deleteTicket($id) {
        $stmt = $this->db->prepare("DELETE FROM tickets WHERE id=?");
        return $stmt->execute([$id]);
    }
}
?>
