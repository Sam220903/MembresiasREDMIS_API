<?php

class MembresiasService {

    private $connection;

    public function __construct( $connection ) {
        $this->connection = $connection;
    }

    public function getMembresias() {
        $query = "SELECT nombre, tipo FROM MR_Membresias WHERE activo = 1;";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    public function getMembresia($id) {
        $query = "SELECT nombre, tipo FROM MR_Membresias WHERE id = :id AND activo = 1;";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $membresia = $stmt->fetch(PDO::FETCH_ASSOC);
        return $membresia ?: null;
    }

    public function mejoraMembresia($id, $data) {
        $query = "UPDATE MR_Membresias 
        SET tipo = :tipo
        WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':tipo', $data["tipo"]);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    public function postMembresias($data) {
        $query = "INSERT INTO MR_Membresias (nombre, tipo) VALUES (:nombre, :tipo)";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':tipo', $data['tipo']);
        $stmt->execute();
        return $this->connection->lastInsertId();
    }

    public function deleteMembresias($id) {
        // $query = 'DELETE FROM MR_Membresias WHERE id = :id';
        // $stmt = $this->connection->prepare($query);
        // $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        // $stmt->execute();
        $query = "UPDATE MR_Membresias SET activo = 0 WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}