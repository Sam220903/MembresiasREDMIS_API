<?php

class MembresiasController{
    private $membresiasService;

    public function __construct($membresiasService )
    {
        $this->membresiasService = $membresiasService;

    }

    public function getMembresias($request){
        if ($request['REQUEST_METHOD'] !== 'GET') throw new Exception('El endpoint no soporta este método');
        return $this->membresiasService->getMembresias();
    }
}