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

    public function updateRequestStatus($requestId, $status, $reason = null) {
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
}