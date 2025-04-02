<?php

class PaisesService{
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllPaises() {
        $sql = "SELECT id, nombre FROM MR_Paises";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}