<?php

class SolicitudesMembresiasService {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getSolicitudesMembresias() {
        $query = "SELECT * FROM MR_SolicitudesMembresia";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $solicitudesMembresias = [];
        foreach ($results as $row) {
            $solicitudesMembresia = new SolicitudesMembresias(
                $row['id'],
                $row['estado'],
                $row['fecha_solicitud'],
                $row['fecha_respuesta'],
                $row['comentarios'],
                $row['revisado_por']
            );
            $solicitudesMembresias[] = $solicitudesMembresia->toJson();
        }

        return $solicitudesMembresias;
    }
}