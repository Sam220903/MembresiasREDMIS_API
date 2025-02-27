<?php

class MembersController {
    public function __construct(private readonly MembersService $service) {}

    // Procesar las solicitudes según su tipo (recurso o colección)
    public function processRequest(string $method, ?string $id): void {
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    // Procesar solicitudes de un solo miembro
    public function processResourceRequest(string $method, string $id): void {
        switch ($method) {
            case 'GET':
                $member = $this->service->getMemberById($id);
                if ($member) {
                    echo json_encode($member);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "Member not found"]);
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(["message" => "Method Not Allowed"]);
                break;
        }
    }

    // Procesar solicitudes de todos los miembros
    public function processCollectionRequest(string $method): void {
        switch ($method) {
            case 'GET':
                $members = $this->service->getAllMembers();
                echo json_encode($members);
                break;
            default:
                http_response_code(405);
                echo json_encode(["message" => "Method Not Allowed"]);
                break;
        }
    }
}

?>
