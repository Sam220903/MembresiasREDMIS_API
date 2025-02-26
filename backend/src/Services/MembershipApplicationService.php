<?php

namespace Backend\Services;

use App\Models\MembershipApplication;

class MembershipApplicationService {
    private $membershipApplicationModel;

    public function __construct() {
        $this->membershipApplicationModel = new MembershipApplication();
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