<?php
class PaisesController{
    private $service;
    
    public function __construct(PaisesService $service){
        $this->service = $service;
        // Agregar el payload cuando esa parte este terminada
    }

    public function handleRequest($method, $data)
    {
        switch ($method) {
            case 'GET':
                $this->listOfPaises();
                break;
            case 'POST':
                $this->createPais( $data['nombre'] );
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
                echo json_encode(['message' => 'Método no permitido']);
                break;
        }
    }
    
    public function listOfPaises(){
        $universidades = $this->service->getAllPaises();
        $universidades = TypeCaster::castRows($universidades);
        echo json_encode($universidades);
    }

    public function createPais($nombre){
        $pais = $this->service->createPais($nombre);
        if ($pais) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Pais creado correctamente']);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['message' => 'Error al crear el pais']);
        }
    }
}