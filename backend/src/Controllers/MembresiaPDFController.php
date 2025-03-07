<?php
  
    class MembresiaPDFController{
        private $membresiasService;
        public function __construct($membresiasService){

            $this->membresiasService=$membresiasService;

        

        }
        public function handleRequest($request, $id, $data){

            if ($request['REQUEST_METHOD'] === 'GET' && $id){

                return $this->getMembresiaPDF($id);
            }
            if ($request['REQUEST_METHOD'] === 'PATCH'){

                return $this -> mejoraMembresia($id, $data);

            } else {
                throw new Exception('API no valida');
            }
        }
        public function mejoraMembresia ($id, $data){
            
            return $this->membresiasService->mejoraMembresia($id, $data);
        }

        public function getMembresiaPDF($id){
            require_once(__DIR__ . '/../../../lib/fpdf186/fpdf.php');
            $pdfData = $this->membresiasService->getMembresia($id);

            if (!$pdfData) {
                throw new Exception('No se encontró la membresía.');
            }
    
            // Define the filename
            $fileName = $pdfData['nombre'] . "CredencialPDF.pdf";
            $filePath = __DIR__ . '../pdfs/' . $fileName;
    
            // Create PDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(40, 10, 'Credencial de Membresia');
            $pdf->Ln(10);
    
            // Add membership details
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(40, 10, 'ID: ' . $pdfData['id']);
            $pdf->Ln(10);
            $pdf->Cell(40, 10, 'Nombre: ' . $pdfData['nombre']);
            $pdf->Ln(10);
            $pdf->Cell(40, 10, 'Fecha de Inicio: ' . $pdfData['fecha_inicio']);
            $pdf->Ln(10);
            $pdf->Cell(40, 10, 'Fecha de Fin: ' . $pdfData['fecha_fin']);
            $pdf->Ln(10);
            $pdf->Cell(40, 10, 'Tipo: ' . $pdfData['tipo']);
            $pdf->Ln(10);
    
            // Save the file
            $pdf->Output($filePath, 'F');
    
            return ['message' => 'PDF generado con éxito', 'path' => $filePath];
        }
    
    }

