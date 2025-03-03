<?php
namespace Controllers;

use Services\MembresiaService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MembresiaController {
    private $membresiaService;

    public function __construct() {
        $this->membresiaService = new MembresiaService();
    }

    public function crearMembresia(Request $request, Response $response) {
        $datos = $request->getParsedBody();

        if (!isset($datos['usuario_id']) || !isset($datos['tipo'])) {
            return $response->withJson(['error' => 'Faltan datos obligatorios'], 400);
        }

        $resultado = $this->membresiaService->crearMembresia($datos['usuario_id'], $datos['tipo']);

        if ($resultado) {
            return $response->withJson(['mensaje' => 'Membresía creada correctamente'], 201);
        } else {
            return $response->withJson(['error' => 'No se pudo crear la membresía'], 500);
        }
    }
}
