<?php
class UniversidadesController{
    private $service;
    
    public function __construct(UniversidadesService $service){
        $this->service = $service;
        // Agregar el payload cuando esa parte este terminada
    }
    
    public function listOfUniversidades(){
        $universidades = $this->service->getAllUniversidades();
        
        // Formatear el resultado para incluir id y nombre
        $resultado = array_map(function($universidad) {
            return [
                'id' => $universidad['id'], // Asegúrate de que el array tenga este campo
                'nombre' => $universidad['nombre']
            ];
        }, $universidades);
        
        header('Content-Type: application/json');
        echo json_encode($resultado);
    }
}