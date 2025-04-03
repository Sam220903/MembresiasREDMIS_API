<?php
class EstadosController{
    private $service;
    
    public function __construct(EstadosService $service){
        $this->service = $service;
        // Agregar el payload cuando esa parte este terminada
    }
    
    public function listOfEstados(){
        $universidades = $this->service->getAllEstados();
        echo json_encode($universidades);
    }
}