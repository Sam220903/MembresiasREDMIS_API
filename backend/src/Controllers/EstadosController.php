<?php
class EstadosController{
    private $service;
    
    public function __construct(EstadosService $service){
        $this->service = $service;
        // Agregar el payload cuando esa parte este terminada
    }
    
    public function handleRequest($method, $data)
    {
        switch ($method) {
            case 'GET':
                $this->listOfEstados();
                break;
            case 'POST':
                $this->createEstado($data);
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
                echo json_encode(['message' => 'Método no permitido']);
                break;
        }
    }
    public function listOfEstados(){
        $estados = $this->service->getAllEstados();
        $estados = TypeCaster::castRows($estados);
        header('Content-Type: application/json');
        echo json_encode($estados);
    }

    public function createEstado($data){
        $estado = $this->service->createEstado($data);
        if ($estado) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Estado agregado correctamente']);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['message' => 'Error al agregar el estado']);
        }
    }
}