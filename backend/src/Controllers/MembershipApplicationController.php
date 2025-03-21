<?php

class MembershipApplicationController {
    private $membershipApplicationService;

    public function __construct(MembershipApplicationService $membershipApplicationService) {
        $this->membershipApplicationService = $membershipApplicationService;
    }

    private function getUserFromToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (strpos($authHeader, 'Bearer ') !== 0) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Token no proporcionado"]);
            exit;
        }

        $token = substr($authHeader, 7);
        $payload = json_decode(base64_decode(explode('.', $token)[1] ?? ''), true);

        if (!$payload || !isset($payload['id'])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Token inválido"]);
            exit;
        }

        return $payload;
    }

    public function registerMembership() {
        $userPayload = $this->getUserFromToken();

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['MR_Membresias_id']) || !isset($data['cv']) || !isset($data['telefono'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Todos los campos requeridos deben ser proporcionados."]);
            exit;
        }

        try {
            $result = $this->membershipApplicationService->createApplication($userPayload['id'], $data);
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Solicitud de membresía enviada exitosamente.", "data" => $result]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
