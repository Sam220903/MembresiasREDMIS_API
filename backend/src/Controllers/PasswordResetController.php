<?php

class PasswordResetController {
    private $passwordResetService;
    private $mailerService;

    public function __construct(PasswordResetService $passwordResetService, MailerService $mailerService) {
        $this->passwordResetService = $passwordResetService;
        $this->mailerService = $mailerService;
    }

    public function requestReset() {
        $this->processCollectionRequest($_SERVER['REQUEST_METHOD'] ?? 'POST', 'request');
    }

    public function confirmReset() {
        $this->processCollectionRequest($_SERVER['REQUEST_METHOD'] ?? 'POST', 'confirm');
    }

    // Ninguna de las dos acciones opera sobre un recurso identificado por id
    // (el "id" es el código, que va en el cuerpo, no en la URL), así que solo
    // existe la rama de colección.
    private function processCollectionRequest(string $method, string $action) {
        switch ($method) {
            case 'POST':
                if ($action === 'request') {
                    $this->doRequestReset();
                } else {
                    $this->doConfirmReset();
                }
                break;

            default:
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Método no soportado para esta colección']);
                break;
        }
    }

    private function doRequestReset() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'El email es requerido']);
            return;
        }

        try {
            $result = $this->passwordResetService->requestReset($data['email']);

            if ($result) {
                $this->mailerService->sendPasswordResetCode($result['email'], $result['nombre'], $result['code']);
            }

            // Respuesta genérica siempre: no revela si el email existe o no en el sistema.
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Si el email está registrado, recibirás un código para restablecer tu contraseña.'
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private function doConfirmReset() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['code']) || empty($data['newPassword'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'El código y la nueva contraseña son requeridos']);
            return;
        }

        try {
            $success = $this->passwordResetService->resetPassword($data['code'], $data['newPassword']);

            if (!$success) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'El código es inválido o ha expirado']);
                return;
            }

            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Contraseña actualizada exitosamente']);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
