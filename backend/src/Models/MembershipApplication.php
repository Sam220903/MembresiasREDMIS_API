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
    public function getUserAndMembershipInfo($userId, $membershipId) {
        // Obtener información del usuario
        $stmt = $this->pdo->prepare("SELECT nombre, email FROM MR_Miembros WHERE id = :userId");
        $stmt->execute([':userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new \Exception("Usuario no encontrado.");
        }
    
        // Obtener información de la membresía
        $stmt = $this->pdo->prepare("SELECT nombre FROM MR_Membresia WHERE id = :membershipId");
        $stmt->execute([':membershipId' => $membershipId]);
        $membership = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$membership) {
            throw new \Exception("Tipo de membresía no encontrado.");
        }
    
        return [
            'userName' => $user['nombre'],
            'userEmail' => $user['email'],
            'membershipType' => $membership['nombre']
        ];
    }

    public function create($userId, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO MR_SolicitudesMembresia (MR_Miembros_id, MR_Membresias_id, estado, comentarios) 
                                     VALUES (:userId, :membershipId, 'PENDIENTE', :comentarios)");
        $stmt->execute([
            ':userId' => $userId,
            ':membershipId' => $data['MR_Membresias_id'],
            ':comentarios' => $data['comentarios'] ?? ''
        ]);

        // Register CV in MR_ArchivosMiembros, setting `credencial` as NULL
        $stmt = $this->pdo->prepare("INSERT INTO MR_ArchivosMiembros (MR_Miembros_id, cv, credencial) 
                                     VALUES (:userId, :cv, NULL)");
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