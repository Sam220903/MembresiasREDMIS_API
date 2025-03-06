<?php

class MembershipApplicationService {
    private $membershipApplicationModel;

    public function __construct($dbConnection) {
        $this->membershipApplicationModel = new MembershipApplication($dbConnection);
    }

    public function createApplication($userId, $data) {
        // Verificar si el usuario ya tiene una solicitud pendiente
        $existingRequest = $this->membershipApplicationModel->findPendingRequestByUserId($userId);
        if ($existingRequest) {
            throw new \Exception("Ya existe una solicitud de membresía pendiente para este usuario.");
        }

        // Registrar la solicitud de membresía en MR_SolicitudesMembresia
        return $this->membershipApplicationModel->create($userId, $data);
    }
}