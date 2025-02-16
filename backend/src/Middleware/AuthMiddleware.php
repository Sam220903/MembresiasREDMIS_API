<?php 

    namespace App\Middleware;
    use App\Models\User;

    class AuthMiddleware {
        public static function validate() {
            $headers = getallheaders();
            if (!isset($headers["Authorization"])) {
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => "Token no proporcionado"]);
                exit;
            }

            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $userModel = new User();
            $user = $userModel->findByToken($token);

            if (!$user) {
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => "Token inválido"]);
                exit;
            }

            $_REQUEST["user"] = $user;
        }
    }