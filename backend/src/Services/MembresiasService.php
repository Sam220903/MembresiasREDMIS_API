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
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $membresias = [];
        foreach ($results as $row) {
            $membresia = new Membresias(
                $row['id'],
                $row['nombre'],
                $row['fecha_inicio'],
                $row['fecha_fin'],
                $row['tipo']
            );
            $membresias[] = $membresia;
        }

        return $membresias;
    }
}
