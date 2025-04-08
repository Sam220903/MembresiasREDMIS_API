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

    public function createUniversidad($nombre) {
        $sql = "INSERT INTO MR_Universidades (nombre) VALUES (:nombre)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        return $stmt->execute();
    }
}