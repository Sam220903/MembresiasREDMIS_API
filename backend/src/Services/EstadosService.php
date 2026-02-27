<?php

class EstadosService{
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllEstados() {
        $sql = "SELECT id, nombre, MR_Paises_id as pais_id FROM MR_Estados";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatesPerCountry($country_id) {
        $sql = "SELECT id, nombre FROM MR_Estados WHERE MR_Paises_id = :country_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':country_id', $country_id, PDO::PARAM_INT);
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