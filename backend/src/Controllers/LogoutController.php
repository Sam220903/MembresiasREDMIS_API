<?php

class LogoutController{

    use AuthorizationTrait;

    private $token_service;
    public function __construct(TokenService $token_service){
        $this->token_service = $token_service;
    }

    public function processRequest($method)
    {
        if ($method == 'POST') {
            $this->logout();
        } else {
            http_response_code(405);
            header("Allow: POST");
        }
    }

    public function logout(){
        session_start();

        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? '';

        if ($auth_header === null || !preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token no proporcionado']);
            exit();
        }

        $token = $matches[1];
        $stored_token = $this->token_service->findByToken($token);
        if($stored_token !== null){
            $this->token_service->expireAndRevokeTokens($token);
            http_response_code(200);
            echo json_encode(['message' => 'Cierre de sesión exitoso']);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
        }

        unset($_SESSION['user']);
        session_unset();
        session_destroy();

    }
}