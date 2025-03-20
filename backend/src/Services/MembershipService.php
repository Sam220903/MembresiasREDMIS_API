<?php

class MembershipService {
    private $membershipRequestModel;

    public function __construct($dbConnection) {
        $this->membershipRequestModel = new MembershipRequest($dbConnection);
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
        return $this->membershipRequestModel->updateRequestStatus($id, $status, $reason);
    }
}