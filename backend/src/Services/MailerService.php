<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/../PHPMailer/PHPMailer.php';
require __DIR__ . '/../PHPMailer/Exception.php';
require __DIR__ . '/../PHPMailer/SMTP.php';
class MailerService {
    private $mail;
    private $fromEmail = 'membresias-noreplay@lumacad.com.mx';
    private $fromName = 'Membresias Redmis';
    private $rootPath = __DIR__ . '/../../public/';

    public function __construct() {
        $this->mail = new PHPMailer(true);
        try {
            // Configuración SMTP directa
            $this->mail->isSMTP();
            $this->mail->Host       = 'mail.lumacad.com.mx';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $this->fromEmail;
            $this->mail->Password   = ',9q=29TIL=xO';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = 587;
            
            // Configuración UTF-8
            $this->mail->CharSet = 'UTF-8';  // Establece el charset a UTF-8
            $this->mail->Encoding = 'base64'; // Codificación para el contenido
            
            $this->mail->setFrom($this->fromEmail, $this->fromName, 'UTF-8');
        } catch (Exception $e) {
            error_log("Error en la configuración de PHPMailer: " . $e->getMessage());
        }
    }

    // 📩 Notifica al administrador sobre una nueva solicitud de membresía
    public function notifyAdmin($adminEmail, $userName, $userEmail, $membershipType) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($adminEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = "Nueva Solicitud de Membresía - " . $membershipType;
            
            // Asegurar que el contenido HTML tenga la declaración de charset
            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <h2>Nueva solicitud de membresía recibida</h2>
                    <p><strong>Usuario:</strong> '.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Email:</strong> '.htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Tipo de membresía solicitada:</strong> '.htmlspecialchars($membershipType, ENT_QUOTES, 'UTF-8').'</p>
                    <p>Por favor revisa el sistema para aprobar o rechazar esta solicitud.</p>
                </body>
                </html>
            ';
            
            $this->mail->Body = $htmlContent;
            $this->mail->AltBody = strip_tags($htmlContent); // Versión de texto plano
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar notificación al admin: " . $e->getMessage());
            return false;
        }
    }
    // 📩 Envía confirmación al usuario con su membresía en PDF
    public function sendMembershipApproval($userEmail, $userName, $pdfInfo) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($userEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = htmlspecialchars("Membresía Aceptada", ENT_QUOTES, 'UTF-8');
            
            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <p>Hola <strong>'.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').'</strong>, tu solicitud de membresía ha sido aceptada. 
                    En adjunto encontrarás el archivo PDF de tu membresía.</p>
                </body>
                </html>
            ';
    
            $this->mail->Body = $htmlContent;
            $this->mail->AltBody = "Hola $userName, tu solicitud de membresía ha sido aceptada. En adjunto encontrarás el archivo PDF de tu membresía.";
    
            if (!isset($pdfInfo['path'])) {
                error_log("Error: No se proporcionó la ruta del PDF");
                return false;
            }
            
            $pdfPath = $this->normalizePdfPath($pdfInfo['path']);
            
            if (file_exists($pdfPath) && is_readable($pdfPath)) {
                $fileName = isset($pdfInfo['fileName']) ? $pdfInfo['fileName'] : basename($pdfPath);
                $this->mail->addAttachment($pdfPath, $fileName, 'base64', 'application/pdf');
            } else {
                error_log("El archivo PDF no se encontró o no es legible: " . $pdfPath);
                $this->mail->Body .= '<p><strong>Nota:</strong> Hubo un problema al adjuntar tu membresía. Por favor, contacta con soporte.</p>';
                $this->mail->AltBody .= "\n\nNota: Hubo un problema al adjuntar tu membresía. Por favor, contacta con soporte.";
            }
                            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar confirmación de membresía: " . $this->mail->ErrorInfo);
            return false;
        }
    }

    // 📩 Envía un correo al usuario notificándole que su membresía fue rechazada
    public function sendMembershipRejection($userEmail, $userName, $reason) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($userEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = htmlspecialchars("Membresía Rechazada", ENT_QUOTES, 'UTF-8');
            
            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <p>Hola <strong>'.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').'</strong>, lamentamos informarte que tu solicitud de membresía ha sido rechazada.</p>
                    <p><strong>Razón:</strong> '.htmlspecialchars($reason, ENT_QUOTES, 'UTF-8').'</p>
                    <p>Si tienes dudas, puedes comunicarte con nosotros.</p>
                </body>
                </html>
            ';
    
            $this->mail->Body = $htmlContent;
            $this->mail->AltBody = "Hola $userName,\n\nLamentamos informarte que tu solicitud de membresía ha sido rechazada.\n\nRazón: $reason\n\nSi tienes dudas, puedes comunicarte con nosotros.";
    
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar notificación de rechazo: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    public function enviarCodigoVerificacion(string $email, string $nombre, string $codigo): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email);
            $this->mail->isHTML(true);
            $this->mail->Subject = htmlspecialchars("Tu código de verificación - Redmis", ENT_QUOTES, 'UTF-8');
            
            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <h2>¡Bienvenido/a '.htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8').'!</h2>
                    <p>Gracias por registrarte en nuestra plataforma. Para completar tu registro, por favor utiliza el siguiente código de verificación:</p>
                    <div style="font-size: 24px; font-weight: bold; margin: 20px 0;">'.htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8').'</div>
                    <p>Este código es válido por 24 horas.</p>
                    <p>Si no solicitaste este registro, por favor ignora este mensaje.</p>
                </body>
                </html>
            ';
            
            $this->mail->Body = $htmlContent;
            $this->mail->AltBody = "Tu código de verificación es: $codigo";
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar código de verificación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Normaliza la ruta del PDF para asegurar que es accesible
     */
    public function normalizePdfPath($path) {
        if (file_exists($path)) {
            return $path;
        }
        
        if (strpos($path, '../pdfs/') === 0) {
            return $this->rootPath . substr($path, 3); 
        }
        
        if (strpos($path, '/') !== 0) {
            return $this->rootPath . $path;
        }
        
        return $path;
    }
}