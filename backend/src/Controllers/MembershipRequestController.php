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
        $method = $_SERVER['REQUEST_METHOD'] ?? 'PATCH';

        if ($id) {
            $this->processAcceptResourceRequest($method, $id);
        } else {
            $this->processAcceptCollectionRequest($method);
        }
    }

    // Aprobar una solicitud siempre actúa sobre un recurso existente (identificado por $id).
    public function processAcceptResourceRequest($method, $id) {
        switch ($method) {
            case 'PATCH':
            case 'POST':
                $this->doAccept($id);
                break;

            default:
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no soportado para este recurso"]);
                break;
        }
    }

    public function processAcceptCollectionRequest($method) {
        switch ($method) {
            default:
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no soportado para esta colección"]);
                break;
        }
    }

    private function doAccept($id) {
        $userPayload = $this->getUserFromToken();

        if ($userPayload["role"] != 1) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Acceso denegado. Se requieren permisos de administrador."]);
            exit;
        }

        try {
            $result = $this->membershipService->updateRequestStatus($id, 'APROBADA');
            $result = TypeCaster::castRow($result);

            $pdfPath = $result['certificatePath'] ?? null;
            $fileName = $result['certificateFileName'] ?? 'Membresia.pdf';

            if ($pdfPath) {
                $this->mailerService->sendMembershipApproval($result['email'], $result['nombre'], [
                    'path' => $pdfPath,
                    'fileName' => $fileName
                ]);
            } else {
                // Si por algún motivo no se pudo generar la credencial, se manda
                // igual el correo de aprobación (sin adjunto) en vez de fallar
                // silenciosamente o adjuntar un archivo que no existe.
                error_log("No se pudo generar la credencial en PDF para la solicitud {$id}; se envía el correo sin adjunto.");
                $this->mailerService->sendMembershipApproval($result['email'], $result['nombre'], []);
            }

            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Solicitud aprobada y correo enviado", "data" => $result]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function rejectMembershipRequest($id) {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'PATCH';

        if ($id) {
            $this->processRejectResourceRequest($method, $id);
        } else {
            $this->processRejectCollectionRequest($method);
        }
    }

    // Rechazar una solicitud siempre actúa sobre un recurso existente (identificado por $id).
    public function processRejectResourceRequest($method, $id) {
        switch ($method) {
            case 'PATCH':
            case 'POST':
                $this->doReject($id);
                break;

            default:
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no soportado para este recurso"]);
                break;
        }
    }

    public function processRejectCollectionRequest($method) {
        switch ($method) {
            default:
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no soportado para esta colección"]);
                break;
        }
    }

    private function doReject($id) {
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

           
            $this->mailerService->sendMembershipRejection($result['email'], $result['nombre'], $data['reason']);
           

            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Solicitud rechazada y correo enviado"]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
