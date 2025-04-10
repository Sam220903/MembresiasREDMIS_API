<?php


class MembershipApplicationService {
    private $membershipApplicationModel;

    public function __construct($dbConnection) {
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
}
