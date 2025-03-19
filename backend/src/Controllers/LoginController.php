<?php

class LoginController
{   
    use AuthorizationTrait;

    private $user_service;
    private $jwt;
    private $token_service;

    public function __construct(UserService $user_service, Jwt $jwt, TokenService $token_service){
        $this->user_service = $user_service;
        $this->jwt = $jwt;
        $this->token_service = $token_service;
    }

    public function processRequest($method){
        if ($method == 'POST') {
            $this->login();
        } else {
            http_response_code(405);
        }
    }

    public function login(){
        session_start();

        $data = json_decode(file_get_contents('php://input'), true);
        $errors = array();

        if (empty($data['email'])) {
            array_push($errors, 'Se requiere el email');
        }

        if (empty($data['password'])) {
            array_push($errors, 'Se requiere la contraseña');
        }

        if (count($errors) != 0) {
            http_response_code(400);
            echo json_encode(['error'=>$errors]);
            return;
        }
 
        $user = $this->user_service->findByEmail($data['email']);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error'=>'Email no encontrado']);
            return;
        } else if (md5($data['password']) !== $user['password_hash']) {
            http_response_code(401);
            echo json_encode(['error'=>'Contraseña errónea']);
            return;
        }

        $payload = array(
            "id" => $user['id'],
            "email" => $user['email'],
            "role" => $user['rol'],
        );

        $token = $this->jwt->createToken($payload);
        $this->token_service->revokeAllTokens($user['id']);
        $this->token_service->saveToken($user['id'], $token, "BEARER", false, false);

        $role = '';
        if($user['rol'] == 1) $role = 'admin';
        else if($user['rol'] == 2) $role = 'user';
        else $role = '';
         
        $_SESSION["user"] = array(
                            'id'=>$user['id'],
                            'name'=>$user['nombre'],
                            'email'=>$user['email'],
                            'role'=> $role);
    
        echo json_encode(['user_id'=>$user['id'],
                          'token'=>$token, 
                          'message'=>"Inicio de sesión exitoso"]);

    }
}