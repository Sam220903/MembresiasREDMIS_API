<?php
    
    class MiembrosController{
        private $miembrosService;
        public function __construct($miembrosService){

            $this->miembrosService=$miembrosService;

        

        }
        public function handleRequest($request, $id, $data){

            if ($_SERVER['REQUEST_METHOD'] === 'GET'){

                if ($id){
                    return $this->getMemberById($id);
                } else {
                    return $this->getAllMembers();
                }

            }
            if ($_SERVER['REQUEST_METHOD'] === 'DELETE'){

                return $this -> deleteMiembro($id);

            } 
            if ($_SERVER['REQUEST_METHOD'] === 'POST'){

                return $this -> postMiembro($data);

            } else {
                throw new Exception('denegado');
            }
        }
        public function postMiembro ($data){
            
            return $this->miembrosService->postMiembro($data);
        }
        public function deleteMiembro( $id) {
            if (empty($id)) throw new Exception('ID requerido');
            return $this->miembrosService->deleteMiembro($id);
        }

        public function getAllMembers(){
            return $this->miembrosService->getAllMembers();
        }

        public function getMemberById($id){
            return $this->miembrosService->getMemberById($id);
        }
    
    }




