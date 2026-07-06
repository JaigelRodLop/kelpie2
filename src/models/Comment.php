<?php
class Comment {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function addComment($ticket_id, $autor_id, $texto) {
        $stmt = $this->db->prepare("INSERT INTO comentarios (ticket_id, autor_id, texto, fecha) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$ticket_id, $autor_id, $texto]);
    }

    public function getCommentsByTicket($ticket_id) {
        $stmt = $this->db->prepare("
            SELECT c.id, c.texto, c.autor_id, u.nombre AS autor_nombre
            FROM comentarios c
            JOIN usuarios u ON c.autor_id = u.id
            WHERE c.ticket_id = ?
            ORDER BY c.id ASC
        ");
        $stmt->execute([$ticket_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteComment($id) {
        $stmt = $this->db->prepare("DELETE FROM comentarios WHERE id=?");
        return $stmt->execute([$id]);
    }
}
?>