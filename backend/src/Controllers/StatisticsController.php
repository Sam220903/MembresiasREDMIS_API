<?php
class StatisticsController{
    public function __construct(private readonly StatisticsService $service){
        // Agregar el payload cuando esa parte este terminada
    }
    // Procesar las solicitudes según su tipo (recurso o colección)
    public function processRequest(string $method, ?string $id) {
        if ($id){
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }
    // Procesar solicitudes de recurso (una sola instancia)
    public function processResourceRequest(string $method, string $id){
        switch($method){
            default: break;
        }
    }
    // Procesar solicitudes de colección (Varias instancias, una tabla)
    public function processCollectionRequest(string $method){
        switch ($method) {
            case 'GET':
                $statistics = $this->service->getStatistics();
                echo json_encode($statistics);
                break;
            
            default:
                break;
        }
    }
}