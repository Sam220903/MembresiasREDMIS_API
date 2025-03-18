<?php

    class UserService {

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