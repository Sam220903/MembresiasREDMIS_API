<?php
    
    class MiembrosController{
        private $miembrosService;
        public function __construct($miembrosService){
            $this->miembrosService = $miembrosService;
        }

        public function handleRequest($request, $id, $data){
            $method = $request['REQUEST_METHOD'];

            switch ($method) {
                case 'GET':
                    return $id ? $this->getMemberById($id) : $this->getAllMembers();
                
                case 'POST':
                    return $this->postMiembro($data);

                case 'DELETE':
                    return $this->deleteMiembro($id);

                case 'PATCH':
                    return $this->updateMember($id, $data);

                default:
                    throw new Exception('denegado');
            }
        }

        public function postMiembro($data){
            return $this->miembrosService->postMiembro($data);
        }

        public function deleteMiembro($id) {
            if (empty($id)) throw new Exception('ID requerido');
            return $this->miembrosService->deleteMiembro($id);
        }

        public function getAllMembers(){
            return $this->miembrosService->getAllMembers();
        }

        public function getMemberById($id){
            return $this->miembrosService->getMemberById($id);
        }

        public function updateMember($id, $data){
            return $this->miembrosService->updateMember($id, $data);
        }
    }
?>




