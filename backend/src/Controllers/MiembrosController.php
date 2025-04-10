<?php
    
    class MiembrosController{
        private $miembrosService;
        private $mailerService;
        public function __construct($miembrosService,$mailerService){
            $this->miembrosService = $miembrosService;
            $this->mailerService = $mailerService;
        }

        public function handleRequest($request, $id, $data) {
            $method = $request['REQUEST_METHOD'];
    
            switch ($method) {
                case 'GET':
                    return $id ? $this->getMemberById($id) : $this->getAllMembers();
                
                case 'POST':
                    return $this->postMiembro($data);
    
                case 'PATCH':
                    if (isset($data['accion']) && $data['accion'] === 'verificar') {
                        return $this->verificarMiembro($id, $data);
                    }
                    return $this->updateMember($id, $data);
    
                case 'DELETE':
                    return $this->deleteMiembro($id);
    
                default:
                    throw new Exception('Método no permitido', 405);
            }
        }

        public function postMiembro($data): array {
            // Validar datos básicos
            if (empty($data['email']) || empty($data['nombre'])) {
                throw new Exception('Nombre y email son requeridos', 400);
            }
        
            // Registrar el miembro (esto genera y guarda el código)
            $result = $this->miembrosService->postMiembro($data);
            $miembroId = $result["id"];
            $codigo = $result["codigo"];
        
            // Enviar código por email
            if (!$this->mailerService->enviarCodigoVerificacion($data['email'], $data['nombre'], $codigo)) {
                throw new Exception('No se pudo enviar el código de verificación', 500);
            }
        
            return [
                'success' => true,
                'message' => 'Usuario registrado. Se ha enviado un código de verificación a tu email.',
                'userId' => $miembroId
            ];
        }

        public function verificarMiembro($id, $data): array {
            if (empty($data['codigo'])) {
                throw new Exception('Código de verificación requerido', 400);
            }
    
            $verificado = $this->miembrosService->verificarMiembro($id, $data['codigo']);
            
            if (!$verificado) {
                throw new Exception('Código de verificación inválido', 400);
            }
    
            return [
                'success' => true,
                'message' => 'Cuenta verificada exitosamente'
            ];
        }
        public function verifyByEmail($data) {
            if (empty($data['email']) || empty($data['code'])) {
                throw new Exception('Email y código son requeridos', 400);
            }
            
            $miembro = $this->miembrosService->getMiembroByEmail($data['email']);
            if (!$miembro) {
                throw new Exception('Usuario no encontrado', 404);
            }
            
            $verificado = $this->miembrosService->verificarMiembro($miembro['id'], $data['code']);
            
            if (!$verificado) {
                return [
                    'success' => false,
                    'message' => 'Código de verificación inválido'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Cuenta verificada exitosamente'
            ];
        }
        
        public function resendVerificationCode($data) {
            if (empty($data['email'])) {
                throw new Exception('Email es requerido', 400);
            }
            
            $result = $this->miembrosService->reenviarCodigo($data['email']);
            if (!$result) {
                throw new Exception('Usuario no encontrado', 404);
            }
            
            // Enviar código por email
            if (!$this->mailerService->enviarCodigoVerificacion($data['email'], $result['nombre'], $result['codigo'])) {
                throw new Exception('No se pudo enviar el código de verificación', 500);
            }
            
            return [
                'success' => true,
                'message' => 'Se ha enviado un nuevo código de verificación a tu email'
            ];
        }
        public function deleteMiembro($id) {
            if (empty($id)) throw new Exception('ID requerido');
            return $this->miembrosService->deleteMiembro($id);
        }

        public function getAllMembers(){
            $members = $this->miembrosService->getAllMembers();
            return TypeCaster::castRows($members);
        }
        

        public function getMemberById($id){
            $member = $this->miembrosService->getMemberById($id);
            return TypeCaster::castRow($member);
        }

        public function updateMember($id, $data){
            return $this->miembrosService->updateMember($id, $data);
        }
        
    }
?>



