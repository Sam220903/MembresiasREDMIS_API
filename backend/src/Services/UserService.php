<?php

    class UserService {
        /*
         * private $userModel;

        public function __construct() {
            $this->userModel = new User();
        }

        public function authenticate($email, $password) {
            $user = $this->userModel->findByEmail($email);
            if ($user && password_verify($password, $user["password_hash"])) {
                $token = bin2hex(random_bytes(32));
                $this->userModel->updateToken($user["id"], $token);
                return ["token" => $token, "user" => ["id" => $user["id"], "nombre" => $user["nombre"], "email" => $user["email"]]];
            }
            throw new \Exception("Credenciales incorrectas");
        }

        public function invalidateToken($token) {
            $user = $this->userModel->findByToken($token);
            if (!$user) throw new \Exception("Token inválido");
            $this->userModel->updateToken($user["id"], null);
        }
         *  */

        private $conn;

        public function __construct(Database $db) {
            $this->conn = $db -> getConnection();
        }

        public function findByEmail($email){
            $sql = "SELECT m.id, CONCAT(m.nombre, ' ', m.apellidos) AS nombre, l.email, l.password_hash, m.MR_TiposUsuario_id as rol
                    FROM MR_Miembros m JOIN MR_Login l ON (m.id = l.MR_Miembros_id) WHERE l.email = :email";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

        }

    }