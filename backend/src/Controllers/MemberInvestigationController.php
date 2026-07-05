<?php
class MemberInvestigationController {
    private MemberInvestigationService $memberInvestigationService;

    public function __construct(MemberInvestigationService $memberInvestigationService) {
        $this->memberInvestigationService = $memberInvestigationService;
    }

    public function processRequest(string $method, ?string $id) {
        header('Content-Type: application/json; charset=UTF-8');
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    // GET /memberInvestigation/{id}    -> asignaciones de un miembro
    // DELETE /memberInvestigation/{id} -> desasignar línea de un miembro
    public function processResourceRequest(string $method, string $id) {
        switch ($method) {
            case 'GET':
                $rows = $this->memberInvestigationService->getByMember((int)$id);
                $rows = TypeCaster::castRows($rows);
                echo json_encode($rows);
                break;

            case 'DELETE':
                $data = json_decode(file_get_contents("php://input"), true);

                if (!$data) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos inválidos o nulos']);
                    return;
                }

                $lineId = $data['line_id'] ?? null;

                if (!$lineId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'El campo line_id es requerido']);
                    return;
                }

                $result = $this->memberInvestigationService->unassign((int)$id, (int)$lineId);
                echo json_encode(['success' => $result]);
                break;

            default:
                http_response_code(405);
                echo json_encode(['error' => 'Método no soportado para este recurso']);
                break;
        }
    }

    // POST /memberInvestigation -> asignar línea a miembro
    public function processCollectionRequest(string $method) {
        switch ($method) {
            case 'POST':
                $data = json_decode(file_get_contents("php://input"), true);

                if (!$data) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos inválidos o nulos']);
                    return;
                }

                $memberId  = $data['member_id'] ?? null;
                $lineId    = $data['line_id']   ?? null;
                $startDate = $data['start_date'] ?? null;

                if (!$memberId || !$lineId || !$startDate) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Los campos member_id, line_id y start_date son requeridos']);
                    return;
                }

                $memberInvestigation = new MemberInvestigation(null, (int)$memberId, (int)$lineId, $startDate);
                $result = $this->memberInvestigationService->assign($memberInvestigation);
                http_response_code($result ? 201 : 500);
                echo json_encode(['success' => $result]);
                break;

            default:
                http_response_code(405);
                echo json_encode(['error' => 'Método no soportado para esta colección']);
                break;
        }
    }
}