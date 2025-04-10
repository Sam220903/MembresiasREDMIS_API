<?php

class SolicitudesMembresiasService {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getSolicitudesMembresias() : array {
        $query = "SELECT s.id, CONCAT(mr.nombre, ' ', mr.apellidos) AS 'nombre', l.email, s.MR_Miembros_id,
                    m.nombre AS 'membresia', s.estado, s.fecha_solicitud, s.fecha_respuesta, s.comentarios,
                    IF( ISNULL(s.revisado_por), 'N/A', CONCAT(u.nombre, ' ', u.apellidos)) AS 'revisor'
                    FROM MR_SolicitudesMembresia s JOIN MR_Membresias m ON (MR_Membresias_id = m.id)
                    JOIN MR_Miembros mr ON (s.MR_Miembros_id = mr.id)
                    JOIN MR_Login l ON (mr.id = l.MR_Miembros_id)
                    LEFT JOIN MR_Miembros u ON (revisado_por = u.id)
                    ORDER BY s.id;";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $responseData = array();

        foreach ($results as $row) {
            $solicitud = [
                'id' => $row['id'],
                'nombre_usuario' => $row['nombre'],
                'email_usuario' => $row['email'],
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

    public function getSolicitudporID($id) {
        $query = "SELECT s.id, CONCAT(u.nombre, ' ', u.apellidos) AS nombre, l.email, 
                        m.nombre AS membresia, s.estado, s.fecha_solicitud, 
                        s.comentarios, a.cv
                    FROM MR_SolicitudesMembresia s
                    LEFT JOIN MR_Membresias m ON s.MR_Membresias_id = m.id
                    LEFT JOIN MR_Miembros u ON s.MR_Miembros_id = u.id
                    LEFT JOIN MR_Login l ON u.id = l.MR_Miembros_id
                    LEFT JOIN MR_ArchivosMiembros a 
                        ON s.MR_Miembros_id = a.MR_Miembros_id
                        AND (a.fecha_subida = (
                            SELECT MAX(a1.fecha_subida) 
                            FROM MR_ArchivosMiembros a1 
                            WHERE a1.MR_Miembros_id = s.MR_Miembros_id)
                        OR ABS(TIMESTAMPDIFF(MINUTE, a.fecha_subida, s.fecha_solicitud)) <= 10)
                    WHERE s.id = :id;";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}