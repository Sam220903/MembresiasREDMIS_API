<?php
class UniversidadesController{
    private $service;
    
    public function __construct(UniversidadesService $service){
        $this->service = $service;
        // Agregar el payload cuando esa parte este terminada
    }
    
    public function listOfUniversidades(){
        $universidades = $this->service->getAllUniversidades();
        echo json_encode($universidades);
    }
}