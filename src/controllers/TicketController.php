<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Ticket.php';

class TicketController {
    private $ticketModel;

    public function __construct($db) {
        $this->ticketModel = new Ticket($db);
    }

    public function create($titulo, $descripcion, $prioridad, $usuario_id) {
        $this->ticketModel->createTicket($titulo, $descripcion, $prioridad, $usuario_id);
        return ["success" => true, "message" => "Ticket creado correctamente"];
    }

    public function index() {
        return $this->ticketModel->getAllTickets();
    }

    public function show($id) {
        return $this->ticketModel->getTicketById($id);
    }

    public function getByUser($usuario_id) {
        return $this->ticketModel->getTicketsByUser($usuario_id);
    }

    public function getByTecnico($tecnico_id) {
        return $this->ticketModel->getTicketsByTecnico($tecnico_id);
    }

    public function updateStatus($id, $estado, $tecnico_id) {
        $this->ticketModel->updateTicketStatus($id, $estado, $tecnico_id);
        return ["success" => true, "message" => "Estado del ticket actualizado"];
    }

    public function delete($id) {
        $this->ticketModel->deleteTicket($id);
        return ["success" => true, "message" => "Ticket eliminado correctamente"];
    }
}
?>