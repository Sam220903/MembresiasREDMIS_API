<?php

class MembershipCertificateService {
    private $storageDirectory;

    public function __construct(string $storageDirectory = __DIR__ . '/../pdfs/certificates/') {
        $this->storageDirectory = $storageDirectory;
    }

    // Genera el PDF de la credencial de membresía con los datos ya resueltos.
    // (El generador anterior, MembresiaPDFController::getMembresiaPDF, buscaba
    // fecha_inicio/fecha_fin en MR_Membresias -el catálogo de TIPOS de
    // membresía-, donde esas columnas no existen; nunca estuvo enrutado
    // en index.php tampoco, así que no se usa).
    public function generate(string $memberName, string $membershipTypeName, string $fechaInicio, string $fechaFin): array {
        // FPDF vive dentro de backend/ (backend/src/Libraries/fpdf186), no en
        // lib/fpdf186 en la raíz del repo: el Dockerfile solo copia backend/
        // al contenedor (COPY backend/ backend), así que una ruta apuntando
        // fuera de backend/ nunca se encuentra en producción.
        require_once __DIR__ . '/../Libraries/fpdf186/fpdf.php';

        if (!is_dir($this->storageDirectory)) {
            mkdir($this->storageDirectory, 0755, true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', $memberName);
        $fileName = $safeName . '_CredencialPDF_' . uniqid() . '.pdf';
        $filePath = $this->storageDirectory . $fileName;

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Credencial de Membresia', 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Nombre: ' . $memberName, 0, 1);
        $pdf->Cell(0, 10, 'Tipo de membresia: ' . $membershipTypeName, 0, 1);
        $pdf->Cell(0, 10, 'Fecha de inicio: ' . $fechaInicio, 0, 1);
        $pdf->Cell(0, 10, 'Fecha de fin: ' . $fechaFin, 0, 1);

        $pdf->Output($filePath, 'F');

        return [
            'path' => $filePath,
            'fileName' => $fileName
        ];
    }
}
