<?php

class MembershipRequest {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    public function findById($requestId) {
        $stmt = $this->pdo->prepare("SELECT * FROM MR_SolicitudesMembresia WHERE id = :requestId");
        $stmt->execute([':requestId' => $requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateRequestStatus($requestId, $status, $reason = null): array {
        $stmt = $this->pdo->prepare("UPDATE MR_SolicitudesMembresia 
                                     SET estado = :status, fecha_respuesta = NOW(), comentarios = :reason 
                                     WHERE id = :requestId");
        $stmt->execute([
            ':status' => $status,
            ':reason' => $reason,
            ':requestId' => $requestId
        ]);

        // Obtener información del usuario para el envío de correo
        $stmt = $this->pdo->prepare("SELECT m.nombre, l.email 
                                     FROM MR_SolicitudesMembresia s 
                                     JOIN MR_Miembros m ON s.MR_Miembros_id = m.id 
                                     JOIN MR_Login l ON m.id = l.MR_Miembros_id 
                                     WHERE s.id = :requestId");
        $stmt->execute([':requestId' => $requestId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registerMembership($memberId, $membershipId) {
        $stmt = $this->pdo->prepare("INSERT INTO MR_MiembrosMembresias (MR_Miembros_id, MR_Membresias_id, fecha_inicio, fecha_fin, estado) 
                                     VALUES (:memberId, :membershipId, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 'ACTIVA')");

        $stmt->execute([
            ':memberId' => $memberId,
            ':membershipId' => $membershipId,
        ]); 
    }

    public function findMembershipMember($memberId) {
        $stmt = $this->pdo->prepare("SELECT * FROM MR_MiembrosMembresias WHERE MR_Miembros_id = :memberId;");
        $stmt->execute([
            ':memberId' => $memberId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteMembership($memberId) {
        $stmt = $this->pdo->prepare("DELETE FROM MR_MiembrosMembresias WHERE MR_Miembros_id = :memberId;");
        $stmt->execute([
            ':memberId' => $memberId,
        ]);
    }

    public function updateMemberStatus($memberId, $status) {
        $stmt = $this->pdo->prepare("UPDATE MR_Miembros SET MR_EstatusMiembros_id = :status WHERE id = :memberId;");
        $stmt->execute([
            ':status' => $status,
            ':memberId' => $memberId,
        ]);
    }
}