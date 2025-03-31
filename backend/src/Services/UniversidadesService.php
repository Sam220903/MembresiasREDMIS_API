<?php

class UniversidadesService{
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllUniversidades() {
        $sql = "SELECT nombre,id FROM MR_Universidades";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}