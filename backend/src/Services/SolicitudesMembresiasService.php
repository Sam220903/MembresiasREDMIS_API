<?php

class SolicitudesMembresiasService {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getSolicitudesMembresias() {
        $query = "SELECT s.id, s.MR_Miembros_id, m.nombre AS 'membresia', s.estado, s.fecha_solicitud, s.fecha_respuesta, s.comentarios, 
                    IF( ISNULL(s.revisado_por), 'N/A', CONCAT(u.nombre, ' ', u.apellidos)) AS 'revisor'
                    FROM MR_SolicitudesMembresia s JOIN MR_Membresias m ON (MR_Membresias_id = m.id)
                    LEFT JOIN MR_Miembros u ON (revisado_por = u.id)
                    ORDER BY s.id;";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $responseData = array();

        foreach ($results as $row) {
            $solicitud = [
                'id' => $row['id'],
                'usuario' => $this->getUserData($row['MR_Miembros_id']),
                'membresia' => $row['membresia'],
                'estado' => $row['estado'],
                'fecha_solicitud' => $row['fecha_solicitud'],
                'fecha_respuesta' => $row['fecha_respuesta'],
                'comentarios' => $row['comentarios'],
                'revisado_por' => $row['revisor']
            ];
            
            array_push($responseData, $solicitud);
        }

        return $responseData;
    }

    private function getUserData($user_id){
        $query = "SELECT CONCAT(m.nombre, ' ', m.apellidos) AS nombre, l.email
                    FROM MR_Miembros m JOIN mr_db.MR_Login l on m.id = l.MR_Miembros_id
                    WHERE m.id = :user_id;";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}