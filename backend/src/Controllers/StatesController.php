<?php
class StatesController{
    private $service;
    
    public function __construct(StatesService $service){
        $this->service = $service;
        // Agregar el payload cuando esa parte este terminada
    }
    public function handleRequest($method, $data, $id = null)
    {
        if ($id) {
            $this->processResourceRequest($method, $id, $data);
        } else {
            $this->processCollectionRequest($method, $data);
        }
    }

    public function processResourceRequest($method, $id, $data)
    {
        switch ($method) {
            case 'GET':
                $this->getStatesOrFiltered($data);
                break;
            case 'PUT':
                $this->updateState($id, $data, true); // true = requiere actualización completa
                break;
            case 'PATCH':
                $this->updateState($id, $data, false); // false = actualización parcial
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
                echo json_encode(['message' => 'Método no permitido']);
                break;
        }
    }

    public function processCollectionRequest($method, $data)
    {
        switch ($method) {
            case 'GET':
                $this->getStatesOrFiltered($data);
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

    private function getStatesOrFiltered($data)
    {
        if (isset($data['country_id'])) {
            $this->getStatesPerCountry($data['country_id']);
            return;
        }
        $this->getAllStates();
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
    public function updateState($id, $data, bool $isFullUpdate){
        // El id siempre es obligatorio para saber qué registro actualizar, viene de la ruta
        if (!isset($id) || !is_numeric($id)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['message' => 'Falta el id del estado a actualizar']);
            return;
        }

        $id = (int)$id;
        $currentState = $this->service->getStateById($id);

        if (!$currentState) {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['message' => 'Estado no encontrado']);
            return;
        }

        if ($isFullUpdate) {
            // PUT: exige todos los campos porque reemplaza el recurso completo
            if (!isset($data['name']) || !isset($data['country_id'])) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['message' => 'Faltan datos requeridos para la actualización completa']);
                return;
            }
            $currentState->setName($data['name']);
            $currentState->setCountryId((int)$data['country_id']);
        } else {
            // PATCH: exige al menos un campo, solo actualiza lo enviado
            if (!isset($data['name']) && !isset($data['country_id'])) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['message' => 'Debe enviar al menos un campo para actualizar']);
                return;
            }
            if (isset($data['name'])) {
                $currentState->setName($data['name']);
            }
            if (isset($data['country_id'])) {
                $currentState->setCountryId((int)$data['country_id']);
            }
        }

        $updated = $this->service->updateState($currentState);

        if ($updated) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Estado actualizado correctamente']);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['message' => 'Error al actualizar el estado']);
        }
    }
}