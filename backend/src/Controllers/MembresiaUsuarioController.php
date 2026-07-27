<?php

class MembresiaUsuarioController {
    private $service;

    public function __construct($conn) {
        $this->service = new MembresiaUsuarioService($conn);
    }

    public function obtenerMembresiaUsuario($data) {
        return $this->processResourceRequest($_SERVER['REQUEST_METHOD'] ?? 'GET', $data);
    }

    public function listarMembresiasUsuario($data) {
        return $this->processCollectionRequest($_SERVER['REQUEST_METHOD'] ?? 'GET', $data);
    }

    // Nota: esta ruta nunca validó el método HTTP (index.php la invoca sin distinguir
    // verbo, y lee el body igual que un POST), así que se aceptan GET y POST para no
    // alterar el comportamiento real hasta confirmar cuál usa el frontend.
    public function processResourceRequest($method, $data) {
        switch ($method) {
            case 'GET':
            case 'POST':
                if (!isset($data['usuarioId'])) {
                    throw new Exception("Se requiere el ID del usuario");
                }

                $usuarioId = $data['usuarioId'];
                $membresias = $this->service->obtenerMembresiasPorUsuarioId($usuarioId);
                $membresias = TypeCaster::castRows($membresias);

                return [
                    'success' => true,
                    'data' => $membresias
                ];

            default:
                throw new Exception('Método no soportado para este recurso');
        }
    }

    public function processCollectionRequest($method, $data) {
        switch ($method) {
            case 'GET':
            case 'POST':
                if (!isset($data['usuarioId'])) {
                    throw new Exception("Se requiere el ID del usuario en el cuerpo de la solicitud");
                }

                $usuarioId = $data['usuarioId'];
                $membresias = $this->service->listarMembresiasUsuario($usuarioId);
                $membresias = TypeCaster::castRows($membresias);

                return [
                    'success' => true,
                    'data' => $membresias
                ];

            default:
                throw new Exception('Método no soportado para esta colección');
        }
    }

    // Misma situación: la ruta original no validaba método HTTP.
    public function actualizarEstadoMembresia($id, $data): array {
        return $this->processActualizarEstadoRequest($_SERVER['REQUEST_METHOD'] ?? 'PATCH', $id, $data);
    }

    public function processActualizarEstadoRequest($method, $id, $data): array {
        switch ($method) {
            case 'PATCH':
            case 'POST':
                if (!isset($id)) {
                    throw new Exception("Se requiere el ID del usuario en el cuerpo de la solicitud");
                }
                $respuesta = $this->service->actualizarEstadoMembresia($id, $data);
                return TypeCaster::castRow($respuesta);

            default:
                throw new Exception('Método no soportado para este recurso');
        }
    }
}
