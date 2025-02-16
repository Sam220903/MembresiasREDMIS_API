<?php 

    namespace Backend\Src\Controllers;
    use Backend\Services\UserService;

    class AuthenticationController{
        private $authenticateService;

        public function __construct($authenticateService){
            $this->authenticateService = $authenticateService;

        }

        public function login(){
            if($_SERVER['REQUEST_METHOD']=='POST'){
                if($data = json_decode(file_get_contents('php://input'),true)){
                    $res = $this->authenticateService->login($data->email, $data->password); // Funcion "teorica", cambiar esto de acuerdo a cambios hechos en servicios y en AuthController
                    return json_encode($res);
                }else{
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "Fallo en json_decode dentro del POST"]);
                }
            }else{
                http_response_code(405);
                return json_encode(["status" => "error", "message" => "Metodo API no permitido"]);
            }
        
        }
}