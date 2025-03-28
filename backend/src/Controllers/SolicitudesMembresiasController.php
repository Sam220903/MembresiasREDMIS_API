<?php

class SolicitudesMembresiasController {
    private $solicitudesMembresiasService;

    public function __construct($solicitudesMembresiasService) {
        $this->solicitudesMembresiasService = $solicitudesMembresiasService;
    }

    public function getSolicitudesMembresias($request) {
        if ($request['REQUEST_METHOD'] !== 'GET') throw new Exception('El endpoint no soporta este método');
        
        $data = $this->solicitudesMembresiasService->getSolicitudesMembresias();
        
        // Configurar encabezados adecuados
        header('Content-Type: application/json; charset=UTF-8');
        
        return $data;
    }
}