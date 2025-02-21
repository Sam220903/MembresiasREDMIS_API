<?php

    class UserService {
        private $userModel;

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
    }