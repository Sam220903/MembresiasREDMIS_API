<?php

class MembershipApplicationController {
    private $membershipApplicationService;
    private $mailerService;
    public function __construct(MembershipApplicationService $membershipApplicationService , MailerService $mailerService) {
        $this->membershipApplicationService = $membershipApplicationService;
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
            // Primero, obtener el nombre y email del usuario
            $userId = $userPayload['id'];
            $userData = $this->membershipApplicationService->getUserData($userId);
            
            // Obtener el tipo de membresía solicitada
            $membershipData = $this->membershipApplicationService->getMembershipData($data['MR_Membresias_id']);
            
            // Registrar la solicitud
            $result = $this->membershipApplicationService->createApplication($userId, $data);
            
            // Ahora sí, enviamos la notificación con los datos completos
            $this->mailerService->notifyAdmin(
                $userData['nombre'] . ' ' . $userData['apellidos'], 
                $userData['email'], 
                $membershipData['nombre']
            );
           
            http_response_code(201);
            echo json_encode([
                "status" => "success", 
                "message" => "Solicitud de membresía enviada exitosamente y correo enviado", 
                "data" => $result
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}