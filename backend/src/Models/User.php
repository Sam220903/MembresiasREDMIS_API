<?php
    namespace App\Models;
    use App\Config\Database;
    use PDO;

    class User {
        private $pdo;

        public function __construct() {
            $this->pdo = Database::connect();
        }

        public function findByEmail($email) {
            $stmt = $this->pdo->prepare("SELECT m.id, m.nombre, m.apellidos, l.password_hash, l.token 
                                        FROM MR_Miembros m 
                                        JOIN MR_login l ON m.id = l.MR_Miembros_id 
                                        WHERE m.email = :email");
            $stmt->execute([":email" => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function updateToken($userId, $token) {
            $stmt = $this->pdo->prepare("UPDATE MR_login SET token = :token WHERE MR_Miembros_id = :id");
            return $stmt->execute([":token" => $token, ":id" => $userId]);
        }

        public function findByToken($token) {
            $stmt = $this->pdo->prepare("SELECT m.id, m.nombre, m.apellidos, m.email 
                                        FROM MR_Miembros m 
                                        JOIN MR_login l ON m.id = l.MR_Miembros_id 
                                        WHERE l.token = :token");
            $stmt->execute([":token" => $token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }