<?php

class SolicitudesMembresiasController {

    private SolicitudesMembresiasService $solicitudesMembresiasService;

    public function __construct(SolicitudesMembresiasService $solicitudesMembresiasService) {
        $this->solicitudesMembresiasService = $solicitudesMembresiasService;
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
                $solicitud = $this->solicitudesMembresiasService->getSolicitudporID($id);
                echo json_encode($solicitud);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Método no soportado para este recurso']);
                break;
        }
    }
    public function processCollectionRequest(string $method) {
        switch ($method) {
            case 'GET':
                $solicitudes = $this->solicitudesMembresiasService->getSolicitudesMembresias();
                echo json_encode($solicitudes);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Método no soportado para esta colección']);
                break;
        }
    }

}



