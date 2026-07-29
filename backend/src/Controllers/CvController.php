<?php

class CvController {
    private $cvService;

    public function __construct(CvService $cvService) {
        $this->cvService = $cvService;
    }

    public function processRequest(string $method, ?string $id) {
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    public function processResourceRequest(string $method, string $id) {
        switch ($method) {
            case 'GET':
                $this->getCvById($id);
                break;
            case 'PATCH':
                $this->updateCv($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Método no soportado para este recurso']);
                break;
        }
    }

    public function processCollectionRequest(string $method) {
        switch ($method) {
            case 'GET':
                $this->listCvs();
                break;
            case 'POST':
                $this->createCv();
                break;
            default:
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Método no soportado para esta colección']);
                break;
        }
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

    // GET /cv -> historial de CVs del miembro autenticado y el más reciente
    private function listCvs() {
        $userPayload = $this->getUserFromToken();

        try {
            $cvs = $this->cvService->getCvsByMember((int)$userPayload['id']);
            $cvsArray = array_map(fn(Cv $cv) => $cv->toArray(), $cvs);
            $latest = $cvs[0] ?? null;

            $latestArray = null;
            if ($latest) {
                $latestArray = $latest->toArray();
                $latestArray['cv_base64'] = $this->cvService->getCvFileBase64($latest);
            }

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'cvs' => $cvsArray,
                    'latest' => $latestArray
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // GET /cv/{id} -> un CV específico (solo si pertenece al miembro autenticado)
    private function getCvById($id) {
        $userPayload = $this->getUserFromToken();

        try {
            $cv = $this->cvService->getCvById((int)$id);

            if (!$cv || $cv->getMemberId() !== (int)$userPayload['id']) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'CV no encontrado']);
                return;
            }

            $cvArray = $cv->toArray();
            $cvArray['cv_base64'] = $this->cvService->getCvFileBase64($cv);

            echo json_encode(['status' => 'success', 'data' => $cvArray]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // POST /cv -> sube un nuevo CV (nuevo registro en el historial)
    private function createCv() {
        $userPayload = $this->getUserFromToken();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['cv_base64'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'El archivo del CV (cv_base64) es requerido']);
            return;
        }

        try {
            $cv = $this->cvService->createCv((int)$userPayload['id'], $data['cv_base64']);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'CV subido exitosamente', 'data' => $cv->toArray()]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // PATCH /cv/{id} -> actualiza el archivo de un CV ya existente
    private function updateCv($id) {
        $userPayload = $this->getUserFromToken();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['cv_base64'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'El archivo del CV (cv_base64) es requerido']);
            return;
        }

        try {
            $existing = $this->cvService->getCvById((int)$id);
            if (!$existing || $existing->getMemberId() !== (int)$userPayload['id']) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'CV no encontrado']);
                return;
            }

            $cv = $this->cvService->updateCv((int)$id, $data['cv_base64']);
            echo json_encode(['status' => 'success', 'message' => 'CV actualizado exitosamente', 'data' => $cv->toArray()]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
