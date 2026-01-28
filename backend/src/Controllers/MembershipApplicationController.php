<?php

class MembershipApplicationController {
    private $membershipApplicationService;
    private $mailerService;
    private $notificationService;
    
    public function __construct(MembershipApplicationService $membershipApplicationService, 
    MailerService $mailerService,
    MembershipNotificationService $notificationService) {
        $this->membershipApplicationService = $membershipApplicationService;
        $this->mailerService = $mailerService;
        $this->notificationService = $notificationService;
    }

    private function getUserFromToken() {
        // Este método se mantiene igual
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
        if (!isset($data['MR_Membresias_id']) || !isset($data['cv']) || !isset($data['telefono']) || !isset($data['cv_base64'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Todos los campos requeridos deben ser proporcionados."]);
            exit;
        }

        try {
            $result = $this->membershipApplicationService->createApplication($userPayload['id'], $data);
            
            // Obtener datos para la notificación
            $membershipData = $this->notificationService->getMembershipApplicationData(
                $userPayload['id'], 
                $data['MR_Membresias_id']
            );
            
            $adminEmail = $this->notificationService->getAdminEmail();
            
            $emailStatus = "pero hubo un problema al enviar el correo";
            
            if ($membershipData && $adminEmail) {
                $userName = $membershipData['nombre'] . ' ' . $membershipData['apellidos'];
                $userEmail = $membershipData['userEmail'];
                $membershipType = $membershipData['membershipType'];
                
                $notificationSent = $this->mailerService->notifyAdmin(
                    $adminEmail, 
                    $userName, 
                    $userEmail, 
                    $membershipType
                );
                
                if ($notificationSent) {
                    $emailStatus = "y correo enviado";
                }
            }
            
            $file_path = '../src/pdfs/cvs/' . $data['cv'];
            // Lanzar error si el archivo no se puede guardar
            if (!PDFProcessor::savePDF($data['cv_base64'], $file_path)) {
                throw new Exception("Error al guardar el archivo PDF.");
            }

            http_response_code(201);
            echo json_encode([
                "status" => "success", 
                "message" => "Solicitud de membresía enviada exitosamente " . $emailStatus, 
                "data" => $result
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}