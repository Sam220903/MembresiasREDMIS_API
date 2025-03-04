<?php

class MembersService {
    private PDO $conn;

    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
    }

    // Obtener todos los miembros con la estructura más detallada
    public function getAllMembers(): array {
        $sql = "
            SELECT 
                MR_Miembros.id,
                CONCAT(MR_Miembros.nombre, ' ', MR_Miembros.apellidos) AS nombre_completo,
                MR_Miembros.genero,
                MR_Miembros.email,
                MR_Universidades.nombre AS universidad,
                MR_Estados.nombre AS estado,
                MR_Paises.nombre AS pais,
                MR_EstatusMiembros.nombre AS estatus,
                MR_TiposUsuario.nombre AS tipo_usuario,
                MR_login.ultimo_acceso
            FROM MR_Miembros
            LEFT JOIN MR_Universidades ON MR_Miembros.MR_Universidades_id = MR_Universidades.id
            LEFT JOIN MR_Estados ON MR_Miembros.MR_Estados_id = MR_Estados.id
            LEFT JOIN MR_Paises ON MR_Miembros.MR_Paises_id = MR_Paises.id
            LEFT JOIN MR_EstatusMiembros ON MR_Miembros.MR_EstatusMiembros_id = MR_EstatusMiembros.id
            LEFT JOIN MR_TiposUsuario ON MR_Miembros.MR_TiposUsuario_id = MR_TiposUsuario.id
            LEFT JOIN MR_login ON MR_Miembros.MR_login_id = MR_login.id;
        ";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un miembro por ID con la misma estructura detallada
    public function getMemberById(string $id): ?array {
        $sql = "
            SELECT 
                MR_Miembros.id,
                CONCAT(MR_Miembros.nombre, ' ', MR_Miembros.apellidos) AS nombre_completo,
                MR_Miembros.genero,
                MR_Miembros.email,
                MR_Universidades.nombre AS universidad,
                MR_Estados.nombre AS estado,
                MR_Paises.nombre AS pais,
                MR_EstatusMiembros.nombre AS estatus,
                MR_TiposUsuario.nombre AS tipo_usuario,
                MR_login.ultimo_acceso
            FROM MR_Miembros
            LEFT JOIN MR_Universidades ON MR_Miembros.MR_Universidades_id = MR_Universidades.id
            LEFT JOIN MR_Estados ON MR_Miembros.MR_Estados_id = MR_Estados.id
            LEFT JOIN MR_Paises ON MR_Miembros.MR_Paises_id = MR_Paises.id
            LEFT JOIN MR_EstatusMiembros ON MR_Miembros.MR_EstatusMiembros_id = MR_EstatusMiembros.id
            LEFT JOIN MR_TiposUsuario ON MR_Miembros.MR_TiposUsuario_id = MR_TiposUsuario.id
            LEFT JOIN MR_login ON MR_Miembros.MR_login_id = MR_login.id
            WHERE MR_Miembros.id = :id;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member ?: null;
    }
}

?>

