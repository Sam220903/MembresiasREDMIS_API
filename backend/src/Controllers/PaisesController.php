<?php
class PaisesController{
    private $service;
    
    public function __construct(PaisesService $service){
        $this->service = $service;
        // Agregar el payload cuando esa parte este terminada
    }
    
    public function listOfPaises(){
        $universidades = $this->service->getAllPaises();
        echo json_encode($universidades);
    }
}