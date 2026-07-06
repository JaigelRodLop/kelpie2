<?php
class Database {
    private $host = "localhost";       // Servidor de BD
    private $db_name = "kelpie_helpdesk"; // Nombre de la BD creada con schema.sql
    private $username = "root";        // Usuario por defecto en XAMPP
    private $password = "";            // Contraseña (vacía en XAMPP por defecto)
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            // Configuración de PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>