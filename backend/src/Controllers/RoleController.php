<?php

class RoleController{
    private $membersService;
    public function __construct($membersService){
        $this->membersService = $membersService;
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
            case 'PATCH':
                return $this->changeRole($id, $data);

            default:
                throw new Exception('denegado');
        }
    }

    public function processCollectionRequest($method, $data){
        switch ($method) {
            default:
                throw new Exception('denegado');
        }
    }

    public function changeRole($id, $data){
        if (empty($data['role'])) throw new Exception('Rol requerido');
        if (empty($id)) throw new Exception('ID requerido');
        $result = $this->membersService->changeRole($id, $data);
        return TypeCaster::castRow($result);
    }
}