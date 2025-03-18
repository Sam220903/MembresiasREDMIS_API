<?php


class MembershipApplicationController {

    use AuthorizationTrait;
    
    private $membershipApplicationService;

    public function __construct(MembershipApplicationService $membershipApplicationService) {
        $this->membershipApplicationService = $membershipApplicationService;
    } 

    public function registerMembership() {

        session_start();

        $user = $_SESSION["user"];

        if (!$user) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Usuario no autenticado."]);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['MR_Membresias_id']) || !isset($data['cv']) || !isset($data['telefono'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Todos los campos requeridos deben ser proporcionados."]);
            exit;
        }

        try {
            $result = $this->membershipApplicationService->createApplication($user['id'], $data);
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Solicitud de membresía enviada exitosamente.", "data" => $result]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}