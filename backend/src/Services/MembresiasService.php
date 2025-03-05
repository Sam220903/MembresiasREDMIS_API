<?php

class MembresiasService {

    private $connection;

    public function __construct( $connection ) {
        $this->connection = $connection;
    }

    public function getMembresias() {
        $query = "SELECT * FROM MR_Membresias";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function postMembresias($data) {
        $query = "INSERT INTO MR_Membresias (nombre, tipo) VALUES (:nombre, :tipo)";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":nombre", $data["nombre"]);
        $stmt->bindValue(":tipo", $data["tipo"]);
        $stmt->execute();
        return $this->connection->lastInsertId();
    }

    public function deleteMembresias($id) {
        $query = "DELETE FROM MR_Membresias WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $id;
    }
        
}
