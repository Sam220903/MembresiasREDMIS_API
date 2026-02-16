<?php
class UniversidadesController{
    private $service;
    
    public function __construct(UniversidadesService $service){
        $this->service = $service;
        // Agregar el payload cuando esa parte este terminada
    }

    public function handleRequest($method, $data)
    {
        switch ($method) {
            case 'GET':
                $this->listOfUniversidades();
                break;
            case 'POST':
                $this->createUniversidad($data);
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
                echo json_encode(['message' => 'Método no permitido']);
                break;
        }
    }
    
    public function listOfUniversidades(){
        $universidades = $this->service->getAllUniversidades();
        $universidades = TypeCaster::castRows($universidades);
        
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

    private function createUniversidad($data){
        $universidad = $this->service->createUniversidad($data);
        if ($universidad) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Universidad creada correctamente']);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['message' => 'Error al crear la universidad']);
        }
    }
}