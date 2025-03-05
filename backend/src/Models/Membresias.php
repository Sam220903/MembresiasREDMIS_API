<?php

// Entidad de la tabla membresias
class Membresias implements JsonSerializable {
    private $id;
    private $nombre;
    private $tipo;

    public function __construct($id, $nombre, $tipo){
        $this->id = $id;
        $this->nombre = $nombre;
        $this->tipo = $tipo;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param mixed $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }


    /**
     * @return mixed
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param mixed $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo
        ];
    }
}