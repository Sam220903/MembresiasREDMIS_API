<?php
class EstadosController{
    public function __construct(private readonly EstadosService $service){
        // Agregar el payload cuando esa parte este terminada
    }
    public function listOfEstados(){
        $universidades = $this->service->getAllEstados();
        echo json_encode($universidades);
    }
}