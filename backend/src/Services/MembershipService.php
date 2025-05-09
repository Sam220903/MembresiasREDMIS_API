<?php

class MembershipService {
    private $membershipRequestModel;

    public function __construct($dbConnection) {
        $this->membershipRequestModel = new MembershipRequest($dbConnection);
    }

    public function updateRequestStatus($id, $status, $reason = null): array {
        $request = $this->membershipRequestModel->findById($id);

        if (!$request) {
            throw new \Exception("Solicitud no encontrada.");
        }

        if ($request['estado'] !== 'PENDIENTE') {
            throw new \Exception("La solicitud ya ha sido procesada.");
        }
        
        if ($status == 'APROBADA') {
            try {
                // Verificar si existe una membresía activa, si existe, eliminarla
                $membership = $this->membershipRequestModel->findMembershipMember($request['MR_Miembros_id']);
                if ($membership) {
                    $this->membershipRequestModel->deleteMembership($request['MR_Miembros_id']);
                }
                $this->membershipRequestModel->registerMembership($request['MR_Miembros_id'], $request['MR_Membresias_id']);
                $this->membershipRequestModel->updateMemberStatus($request['MR_Miembros_id'], 1);
            } catch (\Throwable $th) {
                throw new \Exception("Error al registrar la membresía: " . $th->getMessage());
            }
        } 

        // Actualizar estado de la solicitud con la razón si es rechazada
        return $this->membershipRequestModel->updateRequestStatus($id, $status, $reason);
    }
}