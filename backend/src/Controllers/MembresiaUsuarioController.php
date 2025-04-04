<?php

class MembresiaUsuarioController {
    private $service;

    public function __construct($conn) {
        $this->service = new MembresiaUsuarioService($conn);
    }

    public function obtenerMembresiaUsuario($data) {
        if (!isset($data['usuarioId'])) {
            throw new Exception("Se requiere el ID del usuario");
        }

        $usuarioId = $data['usuarioId'];
        $membresias = $this->service->obtenerMembresiasPorUsuarioId($usuarioId);

        return [
            'success' => true,
            'data' => $membresias
        ];
    }

    public function listarMembresiasUsuario($data) {
        if (!isset($data['usuarioId'])) {
            throw new Exception("Se requiere el ID del usuario en el cuerpo de la solicitud");
        }

        $usuarioId = $data['usuarioId'];
        $membresias = $this->service->listarMembresiasUsuario($usuarioId);

        return [
            'success' => true,
            'data' => $membresias
        ];
    }
}