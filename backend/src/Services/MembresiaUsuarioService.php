<?php

class MembresiaUsuarioService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function obtenerMembresiasPorUsuarioId($usuarioId) {
        $sql = "SELECT mm.id, mm.MR_Miembros_id, mm.MR_Membresias_id, mm.fecha_inicio, mm.fecha_fin, mm.estado, m.nombre as membresia_nombre 
                FROM MR_MiembrosMembresias mm
                JOIN MR_Membresias m ON mm.MR_Membresias_id = m.id
                WHERE mm.MR_Miembros_id = :usuarioId";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarMembresiasUsuario($usuarioId) {
        return $this->obtenerMembresiasPorUsuarioId($usuarioId);
    }
}