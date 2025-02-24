<?php
class TokenService{

    private $conn;

    public function __construct($dataBase)
    {
        $this->conn = $dataBase->getConnection();
    }

    public function saveToken($userID, $token, $type, $expired, $revoked)
    {
        $sql = "INSERT INTO MR_Tokens (token, token_type, expired, revoked, MR_Miembros_id) VALUES (:token, :type, :expired, :revoked, :user_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":token", $token);
        $stmt->bindValue(":type", $type);
        $stmt->bindValue(":expired", $expired, PDO::PARAM_BOOL);
        $stmt->bindValue(":revoked", $revoked, PDO::PARAM_BOOL);
        $stmt->bindValue(":user_id", $userID, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function findValidTokensByUser($userID)
    {
        $sql = "SELECT t.* FROM MR_Tokens t inner join MR_Miembros u on t.MR_Miembros_id = u.id
                WHERE u.id = :user AND (t.expired = false OR t.revoked = false)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":user", $userID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findValidToken($token)
    {
        $sql = "SELECT t.* FROM MR_Tokens t inner join MR_Miembros u on t.MR_Miembros_id = u.id
                WHERE t.token = :token AND (t.expired = false OR t.revoked = false)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":token", $token, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByToken($token)
    {
        $sql = "SELECT * FROM MR_Tokens WHERE token = :token";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":token", $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function expireAndRevokeTokens($token)
    {
        $sql = "UPDATE MR_Tokens SET expired = true, revoked = true WHERE token = :token";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":token", $token);
        $stmt->execute();

    }

    public function revokeAllTokens($userID)
    {
        $tokens =$this->findValidTokensByUser($userID);
        foreach ($tokens as $token) {
            $this->expireAndRevokeTokens($token["token"]);
        }
    }
}