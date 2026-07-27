<?php

class MembresiasService {

    private $connection;

    public function __construct( $connection ) {
        $this->connection = $connection;
    }

    public function getMembresias(): array {
        $query = "SELECT id, nombre, tipo FROM MR_Membresias WHERE activo = 1;";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();

        $membresias = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $membresias[] = new Membresias((int)$row['id'], $row['nombre'], $row['tipo']);
        }
        return $membresias;
    }

    public function getMembresia($id): ?Membresias {
        $query = "SELECT id, nombre, tipo FROM MR_Membresias WHERE id = :id AND activo = 1;";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return new Membresias((int)$row['id'], $row['nombre'], $row['tipo']);
    }

    public function mejoraMembresia($id, $data) {
        $membresia = $this->getMembresia($id);
        if (!$membresia) {
            return false;
        }

        $membresia->setType($data["tipo"]);

        $query = "UPDATE MR_Membresias 
        SET tipo = :tipo
        WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':tipo', $membresia->getType());
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function postMembresias($data) {
        $membresia = new Membresias(null, $data['nombre'], $data['tipo']);

        $query = "INSERT INTO MR_Membresias (nombre, tipo) VALUES (:nombre, :tipo)";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':nombre', $membresia->getName());
        $stmt->bindValue(':tipo', $membresia->getType());
        $stmt->execute();
        return $this->connection->lastInsertId();
    }

    public function deleteMembresias($id) {
        $query = "UPDATE MR_Membresias SET activo = 0 WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) return $id;
        else return false;
    }
}
