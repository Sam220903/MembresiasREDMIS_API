<?php
class PaisesController{
    public function __construct(private readonly PaisesService $service){
        // Agregar el payload cuando esa parte este terminada
    }
    public function listOfPaises(){
        $universidades = $this->service->getAllPaises();
        echo json_encode($universidades);
    }
}