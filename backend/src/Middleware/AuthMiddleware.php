<?php 

    // namespace App\Middleware;
    // use App\Models\User;

    // class AuthMiddleware {
    //     public static function validate() {
    //         $headers = getallheaders();
    //         if (!isset($headers["Authorization"])) {
    //             http_response_code(401);
    //             echo json_encode(["status" => "error", "message" => "Token no proporcionado"]);
    //             exit;
    //         }

    //         $token = str_replace("Bearer ", "", $headers["Authorization"]);
    //         $userModel = new User();
    //         $user = $userModel->findByToken($token);

    //         if (!$user) {
    //             http_response_code(401);
    //             echo json_encode(["status" => "error", "message" => "Token inválido"]);
    //             exit;
    //         }

    //         $_REQUEST["user"] = $user;
    //     }
    // }


    class AuthMiddleware
    {
        private JWT $jwt;
        private array $excluded_routes;
        private RoleMiddleware $role_middleware;

        public function __construct(JWT $jwt, array $excluded_routes)
        {
            $this->jwt = $jwt;
            $this->excluded_routes = $excluded_routes;
            $this->role_middleware = new RoleMiddleware();
        }
        
        public function handleRequest(string $route, string $method, TokenGateway $token_gateway, ?string $id = null)
        {
            if (in_array($route, $this->excluded_routes) || $route === 'login') {
                return [];
            }

            $headers = getallheaders();
            $auth_header = $headers['Authorization'] ?? '';

            if (!str_starts_with($auth_header, 'Bearer ')) {
                http_response_code(401);
                echo json_encode(['error' => 'Token no proporcionado']);
                exit();
            }

            $token = substr($auth_header, 7);
            $payload = $this->jwt->validateToken($token, $token_gateway);

            if (!$payload) {
                http_response_code(401);
                echo json_encode(['error' => 'Token inválido o expirado']);
                exit();
            }

            if (!$this->role_middleware->checkPermissions($payload, $method)) {
                http_response_code(403);
                echo json_encode(['error' => 'No tienes autorización para realizar esta acción']);
                exit();
            }

            return $payload;
        }
    }