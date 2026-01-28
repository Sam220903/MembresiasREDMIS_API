<?php

class FileUploadController {
    private $fileUploadService;

    public function __construct(FileUploadService $fileUploadService) {
        $this->fileUploadService = $fileUploadService;
    }

    public function processRequest() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No se envió ningún archivo']);
            return;
        }

        try {
            // Obtener parámetros adicionales si se envían
            $customFileName = isset($_POST['custom_name']) ? $_POST['custom_name'] : null;
            
            // Procesar la subida del archivo
            $result = $this->fileUploadService->uploadFile($_FILES['file'], $customFileName);
            
            // Si se proporcionó un ID de membresía, podríamos asociar el archivo con esa membresía
            // (esto requeriría una función adicional en un servicio de membresía)
            if (isset($_POST['membership_id'])) {
                $result['membership_id'] = $_POST['membership_id'];
                // Aquí podrías añadir lógica para guardar esta asociación en la base de datos
            }
            
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
