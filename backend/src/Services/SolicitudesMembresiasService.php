<?php

class SolicitudesMembresiasService {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getSolicitudesMembresias() {
        $sql = "SELECT id, estado, fecha_solicitud, fecha_respuesta, comentarios, revisado_por 
                FROM MR_SolicitudesMembresia";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}