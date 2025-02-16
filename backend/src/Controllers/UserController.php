<?php 

    namespace Backend\Src\Controllers;

    class UserController{
        private $userService;

        public function __construct($userService){
            $this->userService = $userService;

        }

        public function login(){
            if($_SERVER['REQUEST_METHOD']!='POST'){
                http_response_code(405);
                return json_encode(['error'=>'Metodo No Permitdo']);

            }
            $data = json_decode(file_get_contents('php://input'),true);

            if(!isset($data['email'])|| !isset($data['password'])){
                http_response_code(400);
                return json_encode(['error'=> 'El Email y la contrasenia son requeridos']);


            }
            try{
                $result = $this->userService->autenticacion($data['email'], $data['password']);
                return json_encode($result);
            }
            catch(\Exception $e){
            http_response_code(401);
            return json_encode(['error' => $e->getMessage()]);  
        }
        
    }
}