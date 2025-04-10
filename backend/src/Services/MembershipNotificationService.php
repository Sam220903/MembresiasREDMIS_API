<?php
class MembershipNotificationService {
    private $pdo;
    
    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }
    
    /**
     * Obtiene el email del administrador
     */
    public function getAdminEmail() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT l.email 
                FROM MR_Login l
                JOIN MR_Miembros m ON l.MR_Miembros_id = m.id
                WHERE m.MR_TiposUsuario_id = 1 
                LIMIT 1
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result && isset($result['email']) ? $result['email'] : null;
        } catch (Exception $e) {
            error_log("Error al obtener email del administrador: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtiene los datos necesarios para la notificación de solicitud de membresía
     */
    public function getMembershipApplicationData($userId, $membershipId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    m.nombre, 
                    m.apellidos, 
                    l.email as userEmail, 
                    mem.nombre as membershipType 
                FROM MR_Miembros m
                JOIN MR_Login l ON m.id = l.MR_Miembros_id
                JOIN MR_Membresias mem ON mem.id = :membershipId
                WHERE m.id = :userId
            ");
            
            $stmt->execute([':userId' => $userId, ':membershipId' => $membershipId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener datos de solicitud: " . $e->getMessage());
            return null;
        }
    }
}