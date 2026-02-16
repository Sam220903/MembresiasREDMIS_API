<?php

class EstadosService{
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllEstados() {
        $sql = "SELECT id, nombre FROM MR_Estados";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function createEstado($data) {
        $sql = "INSERT INTO MR_Estados (nombre, MR_Paises_id) VALUES (:nombre, :pais_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':pais_id', $data['pais_id']);
        return $stmt->execute();
    }
}