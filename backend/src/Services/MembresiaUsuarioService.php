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

    public function actualizarEstadoMembresia($id, $data){
        $status = $data['estado'] == 1 ? 'ACTIVA' : 'INACTIVA';
        $sql = 'UPDATE MR_MiembrosMembresias
                SET estado = :estado
                WHERE MR_Miembros_id = :member_id;';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $status, PDO::PARAM_STR);
        $stmt->bindParam(':member_id', $id, PDO::PARAM_INT);


        
        return [
            'member_id' => $id, 
            'done' => $stmt->execute() && $this->actualizarEstadoMiembro($id, $data)
        ];
    }

    public function actualizarEstadoMiembro($id, $data){
        $sql = 'UPDATE MR_Miembros
                SET MR_EstatusMiembros_id = :estado
                WHERE id = :member_id;';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $data['estado'], PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}