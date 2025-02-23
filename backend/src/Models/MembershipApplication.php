<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class MembershipApplication {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function findPendingRequestByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM MR_Miembros WHERE id = :userId AND MR_EstatusMiembros_id = 1");
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($userId, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO MR_Miembros (id, MR_Membresias_id, telefono, MR_EstatusMiembros_id) VALUES (:userId, :membershipId, :telefono, 1)");
        $stmt->execute([
            ':userId' => $userId,
            ':membershipId' => $data['MR_Membresias_id'],
            ':telefono' => $data['telefono']
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