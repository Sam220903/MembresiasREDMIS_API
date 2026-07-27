<?php

class MembershipApplicationService {
    private $connection;

    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }

    public function createApplication($userId, $data) {
        if ($this->findPendingRequestByUserId($userId)) {
            throw new \Exception("Ya existe una solicitud de membresía pendiente para este usuario.");
        }

        $solicitud = new SolicitudesMembresias(
            null,
            (int)$userId,
            (int)$data['MR_Membresias_id'],
            'PENDIENTE',
            null,
            null,
            $data['comentarios'] ?? ''
        );

        return $this->registerApplication($solicitud, $data['cv']);
    }

    private function findPendingRequestByUserId($userId) {
        $query = "SELECT * FROM MR_SolicitudesMembresia WHERE MR_Miembros_id = :userId AND estado = 'PENDIENTE'";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function registerApplication(SolicitudesMembresias $solicitud, string $cv): array {
        // Registrar la solicitud en MR_SolicitudesMembresia
        $query = "INSERT INTO MR_SolicitudesMembresia (MR_Miembros_id, MR_Membresias_id, estado, comentarios) 
                  VALUES (:userId, :membershipId, :estado, :comentarios)";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':userId', $solicitud->getMiembroId(), PDO::PARAM_INT);
        $stmt->bindValue(':membershipId', $solicitud->getMembresiaId(), PDO::PARAM_INT);
        $stmt->bindValue(':estado', $solicitud->getEstado());
        $stmt->bindValue(':comentarios', $solicitud->getComentarios());
        $stmt->execute();

        // Registrar el CV en MR_ArchivosMiembros, dejando `credencial` como NULL
        $query = "INSERT INTO MR_ArchivosMiembros (MR_Miembros_id, cv, credencial) 
                  VALUES (:userId, :cv, NULL)";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':userId', $solicitud->getMiembroId(), PDO::PARAM_INT);
        $stmt->bindValue(':cv', $cv);
        $stmt->execute();

        return [
            'message' => 'Solicitud de membresía creada exitosamente.',
            'user_id' => $solicitud->getMiembroId()
        ];
    }
}
