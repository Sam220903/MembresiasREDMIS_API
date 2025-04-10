<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/../PHPMailer/PHPMailer.php';
require __DIR__ . '/../PHPMailer/Exception.php';
require __DIR__ . '/../PHPMailer/SMTP.php';
class MailerService {
    private $mail;
    private $adminEmail = 'gerardoans28@gmail.com';
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
            $this->mail->setFrom($this->fromEmail, $this->fromName);
        } catch (Exception $e) {
            error_log("Error en la configuración de PHPMailer: " . $e->getMessage());
        }
    }

    // 📩 Notifica al administrador sobre una nueva solicitud de membresía
    public function notifyAdmin($userName = 'Usuario', $userEmail = 'No disponible', $membershipType = 'No especificado'): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($this->adminEmail); // Usar la propiedad adminEmail que ya estaba definida
            $this->mail->isHTML(true);
            $this->mail->Subject = "Nueva Solicitud de Membresía - " . $membershipType;
            
            $htmlContent = "
                <h2>Nueva solicitud de membresía recibida</h2>
                <p><strong>Usuario:</strong> $userName</p>
                <p><strong>Email:</strong> $userEmail</p>
                <p><strong>Tipo de membresía solicitada:</strong> $membershipType</p>
                <p>Por favor revisa el sistema para aprobar o rechazar esta solicitud.</p>
            ";
            
            $this->mail->Body = $htmlContent;
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
            $this->mail->Subject = "Membresía Aceptada";
            $this->mail->Body    = "<p>Hola <strong>$userName</strong>, tu solicitud de membresía ha sido aceptada. 
                                    En adjunto encontrarás el archivo pdf de tu membresía.</p>";

            if (!isset($pdfInfo['path'])) {
                error_log("Error: No se proporcionó la ruta del PDF");
                return false;
            }
            
            $pdfPath = $this->normalizePdfPath($pdfInfo['path']);
            
            if (file_exists($pdfPath) && is_readable($pdfPath)) {
                $fileName = isset($pdfInfo['fileName']) ? $pdfInfo['fileName'] : basename($pdfPath);
                $this->mail->addAttachment($pdfPath, $fileName);
            } else {
                error_log("El archivo PDF no se encontró o no es legible: " . $pdfPath);
                $this->mail->Body .= "<p><strong>Nota:</strong> Hubo un problema al adjuntar tu membresía. Por favor, contacta con soporte.</p>";
            }
                            
            $this->mail->send();
            return true;
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
            $this->mail->Subject = "Membresía Rechazada";
            $this->mail->Body    = "<p>Hola <strong>$userName</strong>, lamentamos informarte que tu solicitud de membresía ha sido rechazada.</p>
                                    <p><strong>Razón:</strong> $reason</p>
                                    <p>Si tienes dudas, puedes comunicarte con nosotros.</p>";

            $this->mail->send();
            return true;
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
            $this->mail->Subject = "Tu código de verificación - Redmis";
            
            $htmlContent = "
                <h2>¡Bienvenido/a $nombre!</h2>
                <p>Gracias por registrarte en nuestra plataforma. Para completar tu registro, por favor utiliza el siguiente código de verificación:</p>
                <div style='font-size: 24px; font-weight: bold; margin: 20px 0;'>$codigo</div>
                <p>Este código es válido por 24 horas.</p>
                <p>Si no solicitaste este registro, por favor ignora este mensaje.</p>
            ";
            
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