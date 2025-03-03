<?php
    
    class MiembrosController{
        private $miembrosService;
        public function __construct($miembrosService){

            $this->miembrosService=$miembrosService;

        

        }
        public function postMiembro ($request,$data){
            if($request['REQUEST_METHOD']!=='POST') throw new Exception('denegado');
            return $this->miembrosService->postMiembro($data);
        }
    }



   
