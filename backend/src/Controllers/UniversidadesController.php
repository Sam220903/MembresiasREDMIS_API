<?php
class UniversidadesController{
    public function __construct(private readonly UniversidadesService $service){
        // Agregar el payload cuando esa parte este terminada
    }
    public function listOfUniversidades(){
        $universidades = $this->service->getAllUniversidades();
        echo json_encode($universidades);
    }
}