<?php
class EstadosController{
    private $service;
    
    public function __construct(EstadosService $service){
        $this->service = $service;
        // Agregar el payload cuando esa parte este terminada
    }
    
    public function listOfEstados(){
        $estados = $this->service->getAllEstados();
        $estados = TypeCaster::castRows($estados);
        header('Content-Type: application/json');
        echo json_encode($estados);
    }
}