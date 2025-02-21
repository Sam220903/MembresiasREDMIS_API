<?php

    class AuthController {
        private $userService;

        public function __construct($userService) {
            $this->userService = $userService;
        }

        public function login() {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!isset($data['email']) || !isset($data['password'])) {
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "Email y contraseña requeridos"]);
                    return;
                }

                $result = $this->userService->authenticate($data['email'], $data['password']);

                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Login exitoso", "data" => $result]);
            } catch (\Exception $e) {
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
        }

        public function logout() {
            try {
                $headers = getallheaders();
                if (!isset($headers["Authorization"])) {
                    throw new \Exception("No token provided");
                }

                $token = str_replace("Bearer ", "", $headers["Authorization"]);
                $this->userService->invalidateToken($token);

                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Logout exitoso"]);
            } catch (\Exception $e) {
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
        }

        public function getCurrentUser() {
            try {
                if (!isset($_REQUEST["user"])) {
                    throw new \Exception("Usuario no autenticado");
                }

                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Usuario autenticado", "data" => $_REQUEST["user"]]);
            } catch (\Exception $e) {
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
        }
    }