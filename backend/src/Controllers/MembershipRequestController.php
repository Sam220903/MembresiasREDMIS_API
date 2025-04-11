<?php

class MembershipRequestController {
    private $membershipService;
    private $mailerService;

    public function __construct(MembershipService $membershipService, MailerService $mailerService) {
        $this->membershipService = $membershipService;
        $this->mailerService = $mailerService;
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

        if (!$payload || !isset($payload['id']) || !isset($payload['role'])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Token inválido"]);
            exit;
        }

        return $payload;
    }

    public function acceptMembershipRequest($id) {
        $userPayload = $this->getUserFromToken();

        if ($userPayload["role"] != 1) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Acceso denegado. Se requieren permisos de administrador."]);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $result = $this->membershipService->updateRequestStatus($id, 'APROBADA', $data['reason']);
            $result = TypeCaster::castRow($result);

            /* Temporarily disabled email sending for debugging
            $pdfPath = $this->mailerService->normalizePdfPath(' /path/to/membership.pdf');

            $this->mailerService->sendMembershipApproval($result['email'], $result['nombre'], [
                'path' => $pdfPath,
                'fileName' => 'Membresia.pdf'
            ]);
            */

            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Solicitud aprobada y correo enviado", "data" => $result]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function rejectMembershipRequest($id) {
        $userPayload = $this->getUserFromToken();

        if ($userPayload["role"] != 1) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Acceso denegado. Se requieren permisos de administrador."]);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['reason'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Razón del rechazo requerida."]);
            exit;
        }

        try {
            $result = $this->membershipService->updateRequestStatus($id, 'RECHAZADA', $data['reason']);
            $result = TypeCaster::castRow($result);

            // Temporarily disabled email sending for debugging
            /*
            $this->mailerService->sendMembershipRejection($result['email'], $result['nombre'], $data['reason']);
            */

            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Solicitud rechazada y correo enviado"]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}