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

        // Estructura similar a tu ejemplo con un objeto contenedor
        $responseData = [
            "solicitudes" => []
        ];

        foreach ($results as $row) {
            $solicitud = [
                'id' => $row['id'],
                'estado' => $row['estado'],
                'fecha_solicitud' => $row['fecha_solicitud'],
                'fecha_respuesta' => $row['fecha_respuesta'],
                'comentarios' => $row['comentarios'],
                'revisado_por' => $row['revisado_por']
            ];
            
            // Añadir al array interno
            $responseData["solicitudes"][] = $solicitud;
        }

        return $responseData;
    }
}