<?php
class StatesController{
    private $service;
    
    public function __construct(StatesService $service){
        $this->service = $service;
        // Agregar el payload cuando esa parte este terminada
    }

    public function handleRequest($method, $data)
    {
        switch ($method) {
            case 'GET':
                if (isset($data['country_id'])) {
                    $this->getStatesPerCountry($data['country_id']);
                    break;
                }
                $this->getAllStates();
                break;
            case 'POST':
                $this->createState($data);
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
                echo json_encode(['message' => 'Método no permitido']);
                break;
        }
    }
    public function getAllStates(){
        $states = $this->service->getAllStates();
       
        $statesArray = array_map(fn(State $state) =>  $state->toArray(), $states);
        $statesArray = TypeCaster::castRows($statesArray);
        header('Content-Type: application/json');
        echo json_encode($statesArray);
    }

    public function getStatesPerCountry($countryID) {
        $states = $this->service->getStatesPerCountry($countryID);
        $statesArray = array_map(fn(State $state) =>  $state->toArray(), $states);
        $statesArray = TypeCaster::castRows($statesArray);
        header('Content-Type: application/json');
        echo json_encode($statesArray);
    }

    public function createState($data){

        if (!isset($data['name']) || !isset($data['country_id'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['message' => 'Faltan datos requeridos']);
            return;
        }
        
        $newState = new State(null, $data['name'], (int)$data['country_id']);

        $state = $this->service->createState($newState);
        if ($state) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Estado agregado correctamente']);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['message' => 'Error al agregar el estado']);
        }
    }
}