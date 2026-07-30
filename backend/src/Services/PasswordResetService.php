<?php

class PasswordResetService {
    private $connection;
    private $miembrosService;
    private $tokenService;

    private const TOKEN_TYPE = 'PASSWORD_RESET';
    private const EXPIRATION_MINUTES = 30;

    public function __construct($dbConnection, MiembrosService $miembrosService, TokenService $tokenService) {
        $this->connection = $dbConnection;
        $this->miembrosService = $miembrosService;
        $this->tokenService = $tokenService;
    }

    // Genera y guarda un código de recuperación para el email dado. Devuelve
    // los datos del miembro + el código si el email existe, o null si no
    // (el Controller decide no revelar esa diferencia al usuario final, para
    // no permitir enumerar qué correos están registrados).
    public function requestReset(string $email): ?array {
        $member = $this->findMemberByEmail($email);
        if (!$member) {
            return null;
        }

        $code = $this->generateUniqueCode();
        $expirationDate = date('Y-m-d H:i:s', strtotime('+' . self::EXPIRATION_MINUTES . ' minutes'));

        $query = "INSERT INTO MR_Tokens (token, token_type, expired, revoked, MR_Miembros_id, fecha_expiracion) 
                  VALUES (:token, :type, 0, 0, :memberId, :fechaExpiracion)";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':token', $code);
        $stmt->bindValue(':type', self::TOKEN_TYPE);
        $stmt->bindValue(':memberId', $member['id'], PDO::PARAM_INT);
        $stmt->bindValue(':fechaExpiracion', $expirationDate);
        $stmt->execute();

        return [
            'id' => $member['id'],
            'nombre' => $member['nombre'],
            'email' => $member['email'],
            'code' => $code
        ];
    }

    // Valida el código y actualiza la contraseña; regresa false si el código
    // no existe, ya fue usado, o expiró.
    public function resetPassword(string $code, string $newPassword): bool {
        $query = "SELECT * FROM MR_Tokens WHERE token = :token AND token_type = :type";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':token', $code);
        $stmt->bindValue(':type', self::TOKEN_TYPE);
        $stmt->execute();
        $tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tokenRow) {
            return false;
        }

        if ((bool)$tokenRow['expired'] || (bool)$tokenRow['revoked']) {
            return false;
        }

        // El flag "expired" solo se marca al usarse (más abajo) o al cerrar
        // sesión; hay que comparar la fecha de expiración explícitamente.
        if (strtotime($tokenRow['fecha_expiracion']) < time()) {
            return false;
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->miembrosService->updatePassword($tokenRow['MR_Miembros_id'], $hash);
        $this->tokenService->expireAndRevokeToken($code);

        return true;
    }

    private function findMemberByEmail(string $email): ?array {
        $query = "SELECT m.id, m.nombre, l.email 
                  FROM MR_Miembros m 
                  JOIN MR_Login l ON m.id = l.MR_Miembros_id 
                  WHERE l.email = :email";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member ?: null;
    }

    private function generateUniqueCode(): string {
        // Sin caracteres ambiguos (0/O, 1/I/L) ya que el usuario lo captura a mano
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $attempts = 0;

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $attempts++;
        } while ($this->codeExists($code) && $attempts < 5);

        return $code;
    }

    private function codeExists(string $code): bool {
        $query = "SELECT 1 FROM MR_Tokens WHERE token = :token";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':token', $code);
        $stmt->execute();
        return (bool) $stmt->fetch();
    }
}
