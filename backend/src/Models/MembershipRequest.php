<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class MembershipRequest {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT m.id, m.nombre, m.apellidos, m.email, m.MR_EstatusMiembros_id 
                                    FROM MR_Miembros m 
                                    WHERE m.id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status, $reason = null) {
        if ($status === 'rechazada' && $reason) {
            $stmt = $this->pdo->prepare("UPDATE MR_Miembros SET MR_EstatusMiembros_id = :status, motivo_rechazo = :reason WHERE id = :id");
            return $stmt->execute([':status' => $status, ':reason' => $reason, ':id' => $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE MR_Miembros SET MR_EstatusMiembros_id = :status WHERE id = :id");
            return $stmt->execute([':status' => $status, ':id' => $id]);
        }
    }
}