<?php

class MembershipApplicationService {
    private $membershipApplicationModel;
    private $pdo; // Agregar esta propiedad

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection; // Guardar la conexión
        $this->membershipApplicationModel = new MembershipApplication($dbConnection);
    }

    public function createApplication($userId, $data) {
        // Verify if user already has a pending membership request
        $existingRequest = $this->membershipApplicationModel->findPendingRequestByUserId($userId);
        if ($existingRequest) {
            throw new \Exception("Ya existe una solicitud de membresía pendiente para este usuario.");
        }

        // Register membership request in MR_SolicitudesMembresia
        return $this->membershipApplicationModel->create($userId, $data);
    }

    public function getUserData($userId) {
        // Consulta la base de datos para obtener el nombre y email del usuario
        $stmt = $this->pdo->prepare("SELECT nombre, apellidos FROM MR_Miembros WHERE id = :userId");
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMembershipData($membershipId) {
        // Consulta la base de datos para obtener información de la membresía
        $stmt = $this->pdo->prepare("SELECT nombre FROM MR_Membresias WHERE id = :membershipId");
        $stmt->execute([':membershipId' => $membershipId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}