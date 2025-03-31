<?php
class UniversidadesController {
    public function __construct(private readonly UniversidadesService $service) {
        // Agregar el payload cuando esa parte este terminada
    }
    
    public function listOfUniversidades() {
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