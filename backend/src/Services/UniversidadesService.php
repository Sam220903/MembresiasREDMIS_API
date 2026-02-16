<?php

class UniversidadesService{
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllUniversidades() {
        $sql = "SELECT id, nombre FROM MR_Universidades";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUniversidad($data) {
        $sql = "INSERT INTO MR_Universidades (nombre, MR_Paises_id) VALUES (:nombre, :pais_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':pais_id', $data['pais_id']);
        return $stmt->execute();
    }
}