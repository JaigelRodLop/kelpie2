<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Comment.php';

class CommentController {
    private $commentModel;

    public function __construct($db) {
        $this->commentModel = new Comment($db);
    }

    public function create($ticket_id, $autor_id, $texto) {
        if (empty($texto)) {
            return ["success" => false, "message" => "El comentario no puede estar vacío"];
        }
        $this->commentModel->addComment($ticket_id, $autor_id, $texto);
        return ["success" => true, "message" => "Comentario agregado correctamente"];
    }

    public function getByTicket($ticket_id) {
        return $this->commentModel->getCommentsByTicket($ticket_id);
    }

    public function delete($id) {
        $this->commentModel->deleteComment($id);
        return ["success" => true, "message" => "Comentario eliminado correctamente"];
    }
}
?>