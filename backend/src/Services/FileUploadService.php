<?php

class FileUploadService {
    private $uploadDirectory;
    private $allowedExtensions;
    private $maxFileSize;

    public function __construct($uploadDirectory = '../uploads/membership/', $maxFileSize = 5242880) {
        // Crear directorio si no existe
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }
        
        $this->uploadDirectory = $uploadDirectory;
        $this->maxFileSize = $maxFileSize; // 5MB por defecto
        $this->allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
    }

    public function uploadFile($file, $customFileName = null) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Parámetros de archivo inválidos.');
        }

        // Verificar errores de subida
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('El tamaño del archivo excede el límite permitido.');
            case UPLOAD_ERR_PARTIAL:
                throw new Exception('El archivo se subió parcialmente.');
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('No se subió ningún archivo.');
            default:
                throw new Exception('Error desconocido.');
        }

        // Verificar tamaño
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('El archivo excede el tamaño máximo permitido (' . ($this->maxFileSize / 1048576) . 'MB).');
        }

        // Verificar extensión
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception('Tipo de archivo no permitido. Extensiones permitidas: ' . implode(', ', $this->allowedExtensions));
        }

        // Generar nombre único si no se proporciona uno personalizado
        if ($customFileName === null) {
            $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\-\_\.]/', '', $file['name']);
        } else {
            $fileName = $customFileName . '.' . $extension;
        }

        $filePath = $this->uploadDirectory . $fileName;

        // Subir el archivo
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Error al mover el archivo subido.');
        }

        return [
            'success' => true,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_url' => '/uploads/membership/' . $fileName,
            'file_size' => $file['size'],
            'file_type' => $file['type']
        ];
    }
}
