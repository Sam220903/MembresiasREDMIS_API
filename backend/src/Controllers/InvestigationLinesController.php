<?php
class InvestigationLinesController {
    private InvestigationLinesService $investigationLinesService;

    public function __construct(InvestigationLinesService $investigationLinesService) {
        $this->investigationLinesService = $investigationLinesService;
    }

    public function processRequest(string $method, ?string $id) {
        header('Content-Type: application/json; charset=UTF-8');
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    // DELETE /investigationLine/{id} -> borrado lógico (activo = 0)
    public function processResourceRequest(string $method, string $id) {
        switch ($method) {
            case 'DELETE':
                $success = $this->investigationLinesService->deleteLine((int) $id);
                http_response_code($success ? 200 : 500);
                echo json_encode(['success' => $success]);
                break;

            default:
                http_response_code(405);
                echo json_encode(['error' => 'Método no soportado para este recurso']);
                break;
        }
    }

    // GET  /investigationLine -> todas las líneas
    // POST /investigationLine -> crear línea
    public function processCollectionRequest(string $method) {
        switch ($method) {
            case 'GET':
                $lines = $this->investigationLinesService->getAllLines();
                $lines = TypeCaster::castRows($lines);
                echo json_encode($lines);
                break;

            case 'POST':
                $data = json_decode(file_get_contents("php://input"), true);

                if (!$data) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos inválidos o nulos']);
                    return;
                }

                $name = $data['name'] ?? null;

                if (!$name) {
                    http_response_code(400);
                    echo json_encode(['error' => 'El campo name es requerido']);
                    return;
                }

                $line    = new InvestigationLine(null, $name);
                $newId   = $this->investigationLinesService->createLine($line);
                http_response_code($newId !== null ? 201 : 500);
                echo json_encode(['success' => $newId !== null, 'id' => $newId]);
                break;

            default:
                http_response_code(405);
                echo json_encode(['error' => 'Método no soportado para esta colección']);
                break;
        }
    }
}