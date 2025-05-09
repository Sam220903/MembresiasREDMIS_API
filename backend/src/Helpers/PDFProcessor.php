<?php 

class PDFProcessor {
    public static function savePDF($base64String, $filePath) {
        // Decodificar el string base64
        $pdfData = base64_decode($base64String);
        
        // Guardar el archivo PDF en la ruta especificada
        return file_put_contents($filePath, $pdfData);
    }

    public static function getPDF($filePath) {
        // Verificar si el archivo existe
        if (file_exists($filePath)) {
            // Leer el contenido del archivo PDF
            return file_get_contents($filePath);
        } else {
            throw new Exception("El archivo PDF no existe.");
        }
    }
}