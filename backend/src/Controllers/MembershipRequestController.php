<?php

class MembershipRequestController {

    use AuthorizationTrait;
    
    private $membershipService;
    private $mailerService;

    public function __construct(MembershipService $membershipService, MailerService $mailerService) {
        $this->membershipService = $membershipService;
        $this->mailerService = $mailerService;
    }

    public function acceptMembershipRequest($id) {
        $user = $_REQUEST["user"];
        if ($user["role"] !== "admin") {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Acceso denegado. Se requieren permisos de administrador."]);
            exit;
        }

        try {
            $result = $this->membershipService->updateRequestStatus($id, 'APROBADA');

            // Normalizar la ruta del PDF
            $pdfPath = $this->mailerService->normalizePdfPath('/path/to/membership.pdf'); // Ajustar con la ruta real

            // Enviar correo de confirmación al usuario
            $this->mailerService->sendMembershipApproval($result['email'], $result['nombre'], [
                'path' => $pdfPath,
                'fileName' => 'Membresia.pdf'
            ]);

            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Solicitud aprobada y correo enviado", "data" => $result]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function rejectMembershipRequest($id) {
        $user = $_REQUEST["user"];
        if ($user["role"] !== "admin") {
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

            // Enviar correo de rechazo al usuario
            $this->mailerService->sendMembershipRejection($result['email'], $result['nombre'], $data['reason']);

            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Solicitud rechazada y correo enviado"]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}