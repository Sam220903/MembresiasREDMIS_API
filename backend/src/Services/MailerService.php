<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/../PHPMailer/PHPMailer.php';
require __DIR__ . '/../PHPMailer/Exception.php';
require __DIR__ . '/../PHPMailer/SMTP.php';
class MailerService {
    private $mail;
    private $fromEmail;
    private $fromName = 'Membresias Redmis';
    private $rootPath = __DIR__ . '/../../public/';
    // Ruta al logo dentro del backend y el "cid" con el que se referencia
    // desde el HTML (<img src="cid:logoRedmis">). Ajusta la ruta a donde
    // tengas guardado el archivo.
    private $logoPath = __DIR__ . '/../../public/assets/logo-redmis.png';
    private $logoCid = 'logoRedmis';

    public function __construct() {
        // Las credenciales SMTP vienen de variables de entorno; si no están
        // configuradas, se usan los valores que ya traía el proyecto para no
        // cambiar el comportamiento por defecto.
        $smtpHost     = getenv('SMTP_HOST') ?: 'mail.lumacad.com.mx';
        $smtpUser     = getenv('SMTP_USER') ?: 'membresias-noreplay@lumacad.com.mx';
        $smtpPassword = getenv('SMTP_PASSWORD') ?: ',9q=29TIL=xO';
        $smtpPort     = (int) (getenv('SMTP_PORT') ?: 587);

        $this->fromEmail = $smtpUser;

        $this->mail = new PHPMailer(true);
        try {
            // Configuración SMTP directa
            $this->mail->isSMTP();
            $this->mail->Host       = $smtpHost;
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $smtpUser;
            $this->mail->Password   = $smtpPassword;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = $smtpPort;
            
            // Configuración UTF-8
            $this->mail->CharSet = 'UTF-8';  // Establece el charset a UTF-8
            $this->mail->Encoding = 'base64'; // Codificación para el contenido
            
            $this->mail->setFrom($this->fromEmail, $this->fromName, 'UTF-8');
        } catch (Exception $e) {
            error_log("Error en la configuración de PHPMailer: " . $e->getMessage());
        }
    }

    /**
     * Adjunta el logo como imagen embebida (cid) si el archivo existe.
     * Se debe llamar después de clearAttachments() y antes de mail->send().
     * En el HTML se referencia como: <img src="cid:logoRedmis" ...>
     */
    private function attachLogo() {
        if (file_exists($this->logoPath) && is_readable($this->logoPath)) {
            $this->mail->addEmbeddedImage($this->logoPath, $this->logoCid, 'logo-redmis.png');
        } else {
            error_log("Aviso: no se encontró el logo para incrustar en el correo: " . $this->logoPath);
        }
    }

    // 📩 Notifica al administrador sobre una nueva solicitud de membresía
    public function notifyAdmin($adminEmail, $userName, $userEmail, $membershipType) {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->addAddress($adminEmail);
            $this->mail->isHTML(true);
            $this->attachLogo();
            $this->mail->Subject = "Nueva Solicitud de Membresía - " . $membershipType;
            
            // Asegurar que el contenido HTML tenga la declaración de charset
            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <img src="cid:'.$this->logoCid.'" alt="REDMIS" style="max-width:160px; margin-bottom:20px;">
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
            $this->mail->clearAttachments();
            $this->mail->addAddress($userEmail);
            $this->mail->isHTML(true);
            $this->attachLogo();
            $this->mail->Subject = htmlspecialchars("Membresía Aceptada", ENT_QUOTES, 'UTF-8');
            
            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <img src="cid:'.$this->logoCid.'" alt="REDMIS" style="max-width:160px; margin-bottom:20px;">
                    <h2>¡Bienvenido/a la familia REDMIS '.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').'!</h2>
                    <p>Hola <strong>'.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').'</strong>, nos complace informarte que tu solicitud de membresía ha sido <strong>aceptada</strong>. </p> 
                    <p>Nos entusiama mucho tenerte como parte de nuestra familia. Puedes consultar el documento de tu membresia ingresando al sistema en <em>Membresías/Descargar</em>.</p>
                    <hr>
                    <strong><small>RED TEMÁTICA MEXICANA DE INGENIERÍA DE SOFTWARE</small></strong>
                </body>
                </html>
            ';
    
            $this->mail->Body = $htmlContent;
            $this->mail->AltBody = "Hola $userName, tu solicitud de membresía ha sido aceptada. En adjunto encontrarás el archivo PDF de tu membresía.";
    
                            
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
            $this->mail->clearAttachments();
            $this->mail->addAddress($userEmail);
            $this->mail->isHTML(true);
            $this->attachLogo();
            $this->mail->Subject = htmlspecialchars("Membresía Rechazada", ENT_QUOTES, 'UTF-8');
            
            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <img src="cid:'.$this->logoCid.'" alt="REDMIS" style="max-width:160px; margin-bottom:20px;">
                    <h2>Hola '.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').'</h2>
                    <p>Por medio del presente correo, lamentamos informarte que tu solicitud de membresía ha sido <strong>rechazada</strong>.</p>
                    <p><strong>Razón:</strong> '.htmlspecialchars($reason, ENT_QUOTES, 'UTF-8').'</p>
                    <p>Si tienes dudas, puedes comunicarte con nosotros.</p>
                    <hr>
                    <strong><small>RED TEMÁTICA MEXICANA DE INGENIERÍA DE SOFTWARE</small></strong>
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
            $this->mail->clearAttachments();
            $this->mail->addAddress($email);
            $this->mail->isHTML(true);
            $this->attachLogo();
            $this->mail->Subject = htmlspecialchars("Tu código de verificación - Redmis", ENT_QUOTES, 'UTF-8');
            
            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <img src="cid:'.$this->logoCid.'" alt="REDMIS" style="max-width:160px; margin-bottom:20px;">
                    <h2>¡Bienvenido/a '.htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8').'!</h2>
                    <p>Gracias por registrarte en nuestra plataforma. Para completar tu registro, por favor utiliza el siguiente código de verificación:</p>
                    <div style="font-size: 24px; font-weight: bold; margin: 20px 0;">'.htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8').'</div>
                    <p>Este código es válido por 24 horas.</p>
                    <p>Si no solicitaste este registro, por favor ignora este mensaje.</p>
                    <hr>
                    <strong><small>RED TEMÁTICA MEXICANA DE INGENIERÍA DE SOFTWARE</small></strong>
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
    
    // 📩 Confirma al usuario que su solicitud de membresía fue recibida (distinto
    // de notifyAdmin, que avisa al administrador para que la revise)
    public function sendMembershipApplicationReceived($userEmail, $userName, $membershipType) {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->addAddress($userEmail);
            $this->mail->isHTML(true);
            $this->attachLogo();
            $this->mail->Subject = "Solicitud de Membresía Recibida";

            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <img src="cid:'.$this->logoCid.'" alt="REDMIS" style="max-width:160px; margin-bottom:20px;">
                    <h2>Hola '.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').'!</h2>
                    <p>Te notificamos que hemos recibido tu solicitud de membresía de tipo
                    <strong>'.htmlspecialchars($membershipType, ENT_QUOTES, 'UTF-8').'</strong>.</p>
                    <p>Revisaremos con cuidado tu solicitud y te enviaremos pronto nuestra respuesta.</p>
                    <p>Ten en cuenta que este proceso puede demorar de 2 a 5 días hábiles, por lo que agradeceremos tu paciencia en ello.</p>
                    <h4>¡Muchas gracias por tu interés en formar parte de REDMIS!</h4>
                    <hr>
                    <strong><small>RED TEMÁTICA MEXICANA DE INGENIERÍA DE SOFTWARE</small></strong>
                </body>
                </html>
            ';

            $this->mail->Body = $htmlContent;
            $this->mail->AltBody = strip_tags($htmlContent);

            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar confirmación de solicitud recibida: " . $e->getMessage());
            return false;
        }
    }

    // 📩 Notifica al usuario que su membresía fue revocada
    public function sendMembershipRevoked($userEmail, $userName) {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->addAddress($userEmail);
            $this->mail->isHTML(true);
            $this->attachLogo();
            $this->mail->Subject = "Membresía Revocada";

            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <img src="cid:'.$this->logoCid.'" alt="REDMIS" style="max-width:160px; margin-bottom:20px;">
                    <h2>Estimado/a '.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').':</h2>
                    <p>Lamentamos informarte que debido a una decisión interna, nos hemos visto en la decisión de <strong>revocar temporalmente tu membresia</strong></p>
                    <p>Si consideras que esto es un error, por favor comunícate con nosotros.</p>
                    <hr>
                    <strong><small>RED TEMÁTICA MEXICANA DE INGENIERÍA DE SOFTWARE</small></strong>
                </body>
                </html>
            ';

            $this->mail->Body = $htmlContent;
            $this->mail->AltBody = strip_tags($htmlContent);

            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar notificación de revocación: " . $e->getMessage());
            return false;
        }
    }

    // 📩 Notifica al usuario que su membresía fue restablecida
    public function sendMembershipRestored($userEmail, $userName) {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->addAddress($userEmail);
            $this->mail->isHTML(true);
            $this->attachLogo();
            $this->mail->Subject = "Membresía Restablecida";

            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <img src="cid:'.$this->logoCid.'" alt="REDMIS" style="max-width:160px; margin-bottom:20px;">
                    <h2>¡Bienvenido/a de vuelta '.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').'!</h2>
                    <p>Hola <strong>'.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').'</strong>, te informamos que tu membresía ha sido <strong>RESTABLECIDA</strong>.</p>
                    <p>Nos alegra tenerte de vuelta con nosotros, <strong>¡Bienvenido de regreso!</strong></p>
                    <strong><small>RED TEMÁTICA MEXICANA DE INGENIERÍA DE SOFTWARE</small></strong>
                    <hr>
                    <strong><small>RED TEMÁTICA MEXICANA DE INGENIERÍA DE SOFTWARE</small></strong>
                </body>
                </html>
            ';

            $this->mail->Body = $htmlContent;
            $this->mail->AltBody = strip_tags($htmlContent);

            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar notificación de restablecimiento: " . $e->getMessage());
            return false;
        }
    }

    // 📩 Envía el código para restablecer la contraseña
    public function sendPasswordResetCode($userEmail, $userName, $code) {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->addAddress($userEmail);
            $this->mail->isHTML(true);
            $this->attachLogo();
            $this->mail->Subject = "Recuperación de contraseña - Redmis";

            $htmlContent = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body>
                    <img src="cid:'.$this->logoCid.'" alt="REDMIS" style="max-width:160px; margin-bottom:20px;">
                    <p>Hola <strong>'.htmlspecialchars($userName, ENT_QUOTES, 'UTF-8').'</strong>, recibimos una solicitud para restablecer tu contraseña.</p>
                    <p>Usa el siguiente código para continuar:</p>
                    <div style="font-size: 24px; font-weight: bold; margin: 20px 0;">'.htmlspecialchars($code, ENT_QUOTES, 'UTF-8').'</div>
                    <p>Este código es válido por 30 minutos. Si no solicitaste este cambio, ignora este mensaje.</p>
                    <hr>
                    <strong><small>RED TEMÁTICA MEXICANA DE INGENIERÍA DE SOFTWARE</small></strong>
                </body>
                </html>
            ';

            $this->mail->Body = $htmlContent;
            $this->mail->AltBody = "Tu código para restablecer tu contraseña es: $code (válido 30 minutos)";

            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar código de recuperación de contraseña: " . $e->getMessage());
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