<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . "/../PHPMailer/PHPMailer.php";
require __DIR__ . "/../PHPMailer/Exception.php";
require __DIR__ . "/../PHPMailer/SMTP.php";

class MailerService {
    private $mail;
    private $adminEmail;
    private $fromEmail;
    private $fromName;
    private $rootPath;

    public function __construct() {
        // Cargar variables de entorno desde un archivo .env si es necesario
        $this->loadEnvVariables();
        
        // Configurar valores desde variables de entorno
        $this->adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@lumacad.com.mx';
        $this->fromEmail = getenv('FROM_EMAIL') ?: 'membresias-noreplay@lumacad.com.mx';
        $this->fromName = getenv('FROM_NAME') ?: 'Membresias Redmis';
        $this->rootPath = getenv('ROOT_PATH') ?: __DIR__ . '/../../public/index.env';
        
        $this->mail = new PHPMailer(true);
        try {
            // Configuración SMTP
            $this->mail->isSMTP();
            $this->mail->Host       = getenv('SMTP_HOST') ?: 'mail.lumacad.com.mx';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $this->fromEmail;
            $this->mail->Password   = getenv('SMTP_PASSWORD'); // Usando variable de entorno
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = getenv('SMTP_PORT') ?: 587;
            $this->mail->setFrom($this->fromEmail, $this->fromName);
        } catch (Exception $e) {
            error_log("Error en la configuración de PHPMailer: " . $e->getMessage());
        }
    }
    
    /**
     * Método para cargar variables de entorno desde un archivo .env
     * Si estás usando un framework como Laravel, esto no es necesario
     */
    private function loadEnvVariables() {
        $envFile = __DIR__ . '/../../public/index.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    putenv("$key=$value");
                }
            }
        }
    }

    // 📩 Notifica al administrador sobre una nueva solicitud de membresía
    public function notifyAdmin($userName, $userEmail) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($this->adminEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = "Nueva Solicitud de Membresía";
            $this->mail->Body    = "<p>El usuario <strong>$userName</strong> ($userEmail) ha solicitado una membresía.</p>";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar notificación al administrador: " . $this->mail->ErrorInfo);
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

            // Verificar si tenemos información del PDF
            if (!isset($pdfInfo['path'])) {
                error_log("Error: No se proporcionó la ruta del PDF");
                return false;
            }
            
            // Construir la ruta absoluta correcta al PDF
            $pdfPath = $this->normalizePdfPath($pdfInfo['path']);
            
            // Verificar si el archivo existe y es legible
            if (file_exists($pdfPath) && is_readable($pdfPath)) {
                $fileName = isset($pdfInfo['fileName']) ? $pdfInfo['fileName'] : basename($pdfPath);
                $this->mail->addAttachment($pdfPath, $fileName);
            } else {
                error_log("El archivo PDF no se encontró o no es legible: " . $pdfPath);
                // Enviar el correo sin adjunto, pero con un mensaje adicional
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
    
    /**
     * Normaliza la ruta del PDF para asegurar que es accesible
     * 
     */
    private function normalizePdfPath($path) {
        // Si es una ruta absoluta, devuélvela tal cual
        if (file_exists($path)) {
            return $path;
        }
        
        // Si la ruta empieza con ../pdfs/ (como en MembresiaPDFController)
        if (strpos($path, '../pdfs/') === 0) {
            return $this->rootPath . substr($path, 3); 
        }
        
        // Si parece una ruta relativa sin '../'
        if (strpos($path, '/') !== 0) {
            return $this->rootPath . $path;
        }
        
        return $path;
    }
}