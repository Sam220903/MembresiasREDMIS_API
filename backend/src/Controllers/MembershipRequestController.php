<?php

namespace Backend\Src\Controllers;

use Backend\Services\MembershipService;
use App\Middleware\AuthMiddleware;

class MembershipRequestController {
    private $membershipService;

    public function __construct(MembershipService $membershipService) {
        $this->membershipService = $membershipService;
    }

    public function acceptMembershipRequest($id) {
        AuthMiddleware::validate(); // Verifica autenticación

        $user = $_REQUEST["user"];
        if ($user["role"] !== "admin") {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Acceso denegado. Se requieren permisos de administrador."]);
            exit;
        }

        try {
            $result = $this->membershipService->acceptRequest($id);
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Solicitud aceptada exitosamente", "data" => $result]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}