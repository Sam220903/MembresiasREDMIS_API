<?php

class EstadosService{
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllEstados() {
        $sql = "SELECT nombre FROM MR_Estados";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}