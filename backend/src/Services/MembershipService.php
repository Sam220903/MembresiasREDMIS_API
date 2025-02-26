<?php

namespace Backend\Services;

use App\Models\MembershipRequest;

class MembershipService {
    private $membershipRequestModel;

    public function __construct() {
        $this->membershipRequestModel = new MembershipRequest();
    }

    public function updateRequestStatus($id, $status, $reason = null) {
        $request = $this->membershipRequestModel->findById($id);

        if (!$request) {
            throw new \Exception("Solicitud no encontrada.");
        }

        if ($request['estado'] !== 'PENDIENTE') {
            throw new \Exception("La solicitud ya ha sido procesada.");
        }

        // Actualizar estado de la solicitud con la razón si es rechazada
        return $this->membershipRequestModel->updateStatus($id, $status, $reason);
    }
}