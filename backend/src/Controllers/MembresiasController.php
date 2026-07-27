<?php

class MembresiasController{
    private $membresiasService;

    public function __construct($membresiasService )
    {
        $this->membresiasService = $membresiasService;

    }

    public function handleRequest($request, $id, $data){
        $method = $request['REQUEST_METHOD'];

        if ($id) {
            return $this->processResourceRequest($method, $id, $data);
        }

        return $this->processCollectionRequest($method, $data);
    }

    public function processResourceRequest($method, $id, $data){
        switch ($method) {
            case 'GET':
                return $this->getMembresias();

            case 'DELETE':
                return $this->deleteMembresia($id);

            default:
                throw new Exception('denegado');
        }
    }

    public function processCollectionRequest($method, $data){
        switch ($method) {
            case 'GET':
                return $this->getMembresias();

            case 'POST':
                return $this->postMembresia($data);

            default:
                throw new Exception('denegado');
        }
    }

    public function getMembresias(){
        $membresias = $this->membresiasService->getMembresias();
        $membresiasArray = array_map(fn(Membresias $m) => $m->toArray(), $membresias);
        return TypeCaster::castRows($membresiasArray);
    }

    public function postMembresia($data){
        return $this->membresiasService->postMembresias($data);
    }

    public function deleteMembresia($id){
        if (empty($id)) throw new Exception('ID requerido');
        return $this->membresiasService->deleteMembresias($id);
    }
}