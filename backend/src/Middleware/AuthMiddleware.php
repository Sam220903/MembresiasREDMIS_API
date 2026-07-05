<?php
    class AuthMiddleware
    {
        private $jwt;
        private $excluded_routes;
        private $role_middleware;

        public function __construct(JWT $jwt, array $excluded_routes)
        {
            $this->jwt = $jwt;
            $this->excluded_routes = $excluded_routes;
            $this->role_middleware = new RoleMiddleware();
        }
        
        public function handleRequest(string $route, string $method, TokenService $token_gateway, string $id = null): array
        {
            if (in_array($route, $this->excluded_routes) || ($route === 'miembros' && $method === 'POST')|| ($route === 'verify' && $method === 'POST')|| ($route === 'resend_code' && $method === 'POST') || (($route === 'universities' || $route === 'countries' || $route === 'states' ) && $method === 'GET' && $id === null)) {
                return [];
            }

            $headers = getallheaders();
            $auth_header = $headers['Authorization'] ?? '';


            if (strpos($auth_header, 'Bearer ') !== 0) {            // Si se hace el cambio a PHP 8, se puede usar str_starts_with
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