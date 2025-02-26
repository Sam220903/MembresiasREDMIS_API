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
        $stmt = $this->pdo->prepare("SELECT id, MR_Miembros_id, MR_Membresias_id, estado FROM MR_SolicitudesMembresia WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status, $reason = null) {
        $query = "UPDATE MR_SolicitudesMembresia SET estado = :status, fecha_respuesta = NOW(), revisado_por = :revisor";
        $params = [':status' => $status, ':revisor' => $_REQUEST["user"]["id"], ':id' => $id];

        if ($status === 'RECHAZADA' && $reason) {
            $query .= ", comentarios = :reason";
            $params[':reason'] = $reason;
        }

        $query .= " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }
}