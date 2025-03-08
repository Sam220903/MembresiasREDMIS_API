<?php

class SolicitudesMembresias {
    private $id;
    private $estado;
    private $fecha_solicitud;
    private $fecha_respuesta;
    private $comentarios;
    private $revisado_por;

    public function __construct($id, $estado, $fecha_solicitud, $fecha_respuesta, $comentarios, $revisado_por) {
        $this->id = $id;
        $this->estado = $estado;
        $this->fecha_solicitud = $fecha_solicitud;
        $this->fecha_respuesta = $fecha_respuesta;
        $this->comentarios = $comentarios;
        $this->revisado_por = $revisado_por;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getEstado() { return $this->estado; }
    public function setEstado($estado) { $this->estado = $estado; }

    public function getFechaSolicitud() { return $this->fecha_solicitud; }
    public function setFechaSolicitud($fecha_solicitud) { $this->fecha_solicitud = $fecha_solicitud; }

    public function getFechaRespuesta() { return $this->fecha_respuesta; }
    public function setFechaRespuesta($fecha_respuesta) { $this->fecha_respuesta = $fecha_respuesta; }

    public function getComentarios() { return $this->comentarios; }
    public function setComentarios($comentarios) { $this->comentarios = $comentarios; }

    public function getRevisadoPor() { return $this->revisado_por; }
    public function setRevisadoPor($revisado_por) { $this->revisado_por = $revisado_por; }

    public function toJson() {
        return json_encode([
            'id' => $this->id,
            'estado' => $this->estado,
            'fecha_solicitud' => $this->fecha_solicitud,
            'fecha_respuesta' => $this->fecha_respuesta,
            'comentarios' => $this->comentarios,
            'revisado_por' => $this->revisado_por
        ], JSON_PRETTY_PRINT);
    }
}