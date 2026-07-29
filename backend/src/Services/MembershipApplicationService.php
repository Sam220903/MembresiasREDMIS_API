<?php

class MembershipApplicationService {
    private $connection;
    private $cvService;

    public function __construct($dbConnection, CvService $cvService) {
        $this->connection = $dbConnection;
        $this->cvService = $cvService;
    }

    public function createApplication($userId, $data) {
        if ($this->findPendingRequestByUserId($userId)) {
            throw new \Exception("Ya existe una solicitud de membresía pendiente para este usuario.");
        }

        // El CV ya no se pide en esta solicitud: debe existir uno cargado desde el
        // perfil (endpoint /cv o edición de perfil) antes de poder aplicar.
        if (!$this->cvService->getLatestCvByMember((int)$userId)) {
            throw new \Exception("Debes subir tu CV desde tu perfil antes de solicitar una membresía.");
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

        return $this->registerApplication($solicitud);
    }

    private function findPendingRequestByUserId($userId) {
        $query = "SELECT * FROM MR_SolicitudesMembresia WHERE MR_Miembros_id = :userId AND estado = 'PENDIENTE'";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function registerApplication(SolicitudesMembresias $solicitud): array {
        // Registrar la solicitud en MR_SolicitudesMembresia (el CV ya vive aparte,
        // en MR_ArchivosMiembros, gestionado por CvService)
        $query = "INSERT INTO MR_SolicitudesMembresia (MR_Miembros_id, MR_Membresias_id, estado, comentarios) 
                  VALUES (:userId, :membershipId, :estado, :comentarios)";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':userId', $solicitud->getMiembroId(), PDO::PARAM_INT);
        $stmt->bindValue(':membershipId', $solicitud->getMembresiaId(), PDO::PARAM_INT);
        $stmt->bindValue(':estado', $solicitud->getEstado());
        $stmt->bindValue(':comentarios', $solicitud->getComentarios());
        $stmt->execute();

        return [
            'message' => 'Solicitud de membresía creada exitosamente.',
            'user_id' => $solicitud->getMiembroId()
        ];
    }
}
