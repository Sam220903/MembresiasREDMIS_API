<?php
class SolicitudesMembresias {
    private ?int $id;
    private int $miembroId;
    private int $membresiaId;
    private string $estado;
    private ?string $fechaSolicitud;
    private ?string $fechaRespuesta;
    private ?string $comentarios;
    private ?int $revisadoPor;

    public function __construct(
        ?int $id,
        int $miembroId,
        int $membresiaId,
        string $estado = 'PENDIENTE',
        ?string $fechaSolicitud = null,
        ?string $fechaRespuesta = null,
        ?string $comentarios = null,
        ?int $revisadoPor = null
    ) {
        $this->id = $id;
        $this->miembroId = $miembroId;
        $this->membresiaId = $membresiaId;
        $this->estado = $estado;
        $this->fechaSolicitud = $fechaSolicitud;
        $this->fechaRespuesta = $fechaRespuesta;
        $this->comentarios = $comentarios;
        $this->revisadoPor = $revisadoPor;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getMiembroId(): int { return $this->miembroId; }
    public function getMembresiaId(): int { return $this->membresiaId; }
    public function getEstado(): string { return $this->estado; }
    public function getFechaSolicitud(): ?string { return $this->fechaSolicitud; }
    public function getFechaRespuesta(): ?string { return $this->fechaRespuesta; }
    public function getComentarios(): ?string { return $this->comentarios; }
    public function getRevisadoPor(): ?int { return $this->revisadoPor; }

    // Setters
    public function setEstado(string $estado): void { $this->estado = $estado; }
    public function setFechaRespuesta(?string $fechaRespuesta): void { $this->fechaRespuesta = $fechaRespuesta; }
    public function setComentarios(?string $comentarios): void { $this->comentarios = $comentarios; }
    public function setRevisadoPor(?int $revisadoPor): void { $this->revisadoPor = $revisadoPor; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'miembroId' => $this->miembroId,
            'membresiaId' => $this->membresiaId,
            'estado' => $this->estado,
            'fechaSolicitud' => $this->fechaSolicitud,
            'fechaRespuesta' => $this->fechaRespuesta,
            'comentarios' => $this->comentarios,
            'revisadoPor' => $this->revisadoPor,
        ];
    }
}
