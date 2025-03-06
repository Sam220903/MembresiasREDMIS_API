<?php

class MembershipApplication {
    private $pdo;

    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }

    public function findPendingRequestByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM MR_SolicitudesMembresia WHERE MR_Miembros_id = :userId AND estado = 'PENDIENTE'");
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($userId, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO MR_SolicitudesMembresia (MR_Miembros_id, MR_Membresias_id, estado, comentarios) VALUES (:userId, :membershipId, 'PENDIENTE', :comentarios)");
        $stmt->execute([
            ':userId' => $userId,
            ':membershipId' => $data['MR_Membresias_id'],
            ':comentarios' => $data['comentarios'] ?? ''
        ]);

        // Registrar CV en MR_ArchivosMiembros
        $stmt = $this->pdo->prepare("INSERT INTO MR_ArchivosMiembros (MR_Miembros_id, cv) VALUES (:userId, :cv)");
        $stmt->execute([
            ':userId' => $userId,
            ':cv' => $data['cv']
        ]);

        return [
            'message' => 'Solicitud de membresía creada exitosamente.',
            'user_id' => $userId
        ];
    }
}