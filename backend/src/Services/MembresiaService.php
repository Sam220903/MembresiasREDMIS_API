<?php
namespace Services;

use PDO;
use Config\Database;

class MembresiaService {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function crearMembresia($usuario_id, $tipo) {
        try {
            $query = "INSERT INTO membresias (usuario_id, tipo, fecha_creacion) VALUES (:usuario_id, :tipo, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
