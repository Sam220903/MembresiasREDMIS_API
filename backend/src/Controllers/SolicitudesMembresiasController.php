<?php

class SolicitudesMembresiasController {

    public function __construct(private readonly SolicitudesMembresiasService $solicitudesMembresiasService) {}

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
                throw new Exception('Método no soportado para este recurso');
        }
    }
    public function processCollectionRequest(string $method) {
        switch ($method) {
            case 'GET':
                $solicitudes = $this->solicitudesMembresiasService->getSolicitudesMembresias();
                echo json_encode($solicitudes);
                break;
            default:
                throw new Exception('Método no soportado para esta colección');
        }
    }

}



