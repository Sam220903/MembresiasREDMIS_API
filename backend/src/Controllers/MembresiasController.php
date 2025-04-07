<?php

class MembresiasController{
    private $membresiasService;

    public function __construct($membresiasService )
    {
        $this->membresiasService = $membresiasService;

    }

    public function handleRequest($request, $id, $data){
        $method = $request['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                return $this->getMembresias();
            
            case 'POST':
                return $this->postMembresia($data);

            case 'DELETE':
                return $this->deleteMembresia($id);

            default:
                throw new Exception('denegado');
        }
    }

    public function getMembresias(){
        $membresias = $this->membresiasService->getMembresias();
        return TypeCaster::castRows($membresias);
    }

    public function postMembresia($data){
        return $this->membresiasService->postMembresias($data);
    }

    public function deleteMembresia($id){
        if (empty($id)) throw new Exception('ID requerido');
        return $this->membresiasService->deleteMembresias($id);
    }
}