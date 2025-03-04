<?php
 

 class MiembrosService{
    private $connection;
    public function __construct($connection){
        $this->connection=$connection;
    }

    public function postMiembro($data){
        $query="INSERT INTO MR_Miembros (nombre,apellidos,genero,MR_Universidades_id,MR_Estados_id,MR_Paises_id) VALUES (:nombre,:apellidos,:genero,:MR_Universidades_id,:MR_Estados_id,:MR_Paises_id) ";
        $stmt=$this->connection->prepare($query);
        $stmt->bindValue(":nombre",$data["nombre"]);
        $stmt->bindValue(":apellidos",$data["apellidos"]);
        $stmt->bindValue(":genero",$data["genero"]);
        $stmt->bindValue(":MR_Universidades_id",$data["universidad"] ?? null);
        $stmt->bindValue(":MR_Estados_id",$data["estado"] ?? null);
        $stmt->bindValue(":MR_Paises_id",$data["paises"] ?? null);
        //agregar validaciones 

        
        $stmt->execute();
        $miembroId=$this->connection->lastInsertId();
        $this->postLogin($miembroId,$data);
        return $miembroId;

    }

    public function postLogin($miembroId,$data){
        $query="INSERT INTO MR_Login (MR_Miembros_id,email,password_hash) VALUES (:MR_Miembros_id,:email,:password_hash) ";
        $stmt=$this->connection->prepare($query);
        $stmt->bindValue(":MR_Miembros_id",$miembroId);
        $stmt->bindValue(":email",$data["email"]);
        $stmt->bindValue(":password_hash",$data["password"]);

        $stmt->execute(); 
    }

    public function deleteMiembro($id) {
        $query = "DELETE FROM MR_Miembros WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllMembers(): array {
        $sql = "
            SELECT 
                MR_Miembros.id,
                CONCAT(MR_Miembros.nombre, ' ', MR_Miembros.apellidos) AS nombre_completo,
                MR_Miembros.genero,
                MR_Miembros.fecha_registro,
                MR_Miembros.ultima_actualizacion,
                MR_Universidades.nombre AS universidad,
                MR_Estados.nombre AS estado,
                MR_Paises.nombre AS pais,
                MR_EstatusMiembros.nombre AS estatus,
                MR_TiposUsuario.nombre AS tipo_usuario,
                MR_Login.email,
                MR_Login.ultimo_acceso
            FROM MR_Miembros
            LEFT JOIN MR_Universidades ON MR_Miembros.MR_Universidades_id = MR_Universidades.id
            LEFT JOIN MR_Estados ON MR_Miembros.MR_Estados_id = MR_Estados.id
            LEFT JOIN MR_Paises ON MR_Miembros.MR_Paises_id = MR_Paises.id
            LEFT JOIN MR_EstatusMiembros ON MR_Miembros.MR_EstatusMiembros_id = MR_EstatusMiembros.id
            LEFT JOIN MR_TiposUsuario ON MR_Miembros.MR_TiposUsuario_id = MR_TiposUsuario.id
            LEFT JOIN MR_Login ON MR_Miembros.id = MR_Login.MR_Miembros_id;
        ";
        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        // Obtener un miembro por ID con la misma estructura detallada
        public function getMemberById(string $id): ?array {
            $sql = "
                SELECT 
                    MR_Miembros.id,
                    CONCAT(MR_Miembros.nombre, ' ', MR_Miembros.apellidos) AS nombre_completo,
                    MR_Miembros.genero,
                    MR_Miembros.fecha_registro,
                    MR_Miembros.ultima_actualizacion,
                    MR_Universidades.nombre AS universidad,
                    MR_Estados.nombre AS estado,
                    MR_Paises.nombre AS pais,
                    MR_EstatusMiembros.nombre AS estatus,
                    MR_TiposUsuario.nombre AS tipo_usuario,
                    MR_Login.email,
                    MR_Login.ultimo_acceso
                FROM MR_Miembros
                LEFT JOIN MR_Universidades ON MR_Miembros.MR_Universidades_id = MR_Universidades.id
                LEFT JOIN MR_Estados ON MR_Miembros.MR_Estados_id = MR_Estados.id
                LEFT JOIN MR_Paises ON MR_Miembros.MR_Paises_id = MR_Paises.id
                LEFT JOIN MR_EstatusMiembros ON MR_Miembros.MR_EstatusMiembros_id = MR_EstatusMiembros.id
                LEFT JOIN MR_TiposUsuario ON MR_Miembros.MR_TiposUsuario_id = MR_TiposUsuario.id
                LEFT JOIN MR_Login ON MR_Miembros.id = MR_Login.MR_Miembros_id
                WHERE MR_Miembros.id = :id;
            ";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            return $member ?: null;
        }
}


