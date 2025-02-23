<?php

namespace Backend\Services;

use App\Models\MembershipRequest;
use App\Models\User;

class MembershipService {
    private $membershipRequestModel;
    private $userModel;

    public function __construct() {
        $this->membershipRequestModel = new MembershipRequest();
        $this->userModel = new User();
    }

    public function acceptRequest($id) {
        $request = $this->membershipRequestModel->findById($id);

        if (!$request) {
            throw new \Exception("Solicitud no encontrada.");
        }

        if ($request['status'] !== 'pendiente') {
            throw new \Exception("La solicitud ya ha sido procesada.");
        }

        // Actualizar estado de la solicitud
        $this->membershipRequestModel->updateStatus($id, 'aceptada');

        // Crear el usuario como miembro activo
        $newMember = $this->userModel->createMember($request);

        return [
            'message' => 'Membresía creada exitosamente.',
            'member' => $newMember
        ];
    }
}