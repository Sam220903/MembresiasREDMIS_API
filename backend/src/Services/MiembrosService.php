<?php
 

class MiembrosService{
    private $connection;
    public function __construct($connection){
        $this->connection=$connection;
    }

    public function postMiembro($data){
        $query="INSERT INTO MR_Miembros (nombre,apellidos,genero,MR_Universidades_id,MR_Estados_id,MR_Paises_id, MR_TiposUsuario_id) VALUES (:nombre,:apellidos,:genero,:MR_Universidades_id,:MR_Estados_id,:MR_Paises_id, 2) ";
        $stmt=$this->connection->prepare($query);
        $stmt->bindValue(":nombre",$data["nombre"]);
        $stmt->bindValue(":apellidos",$data["apellidos"]);
        $stmt->bindValue(":genero",$data["genero"]);
        $stmt->bindValue(":MR_Universidades_id",$data["universidad"] ?? null);
        $stmt->bindValue(":MR_Estados_id",$data["estado"] ?? null);
        $stmt->bindValue(":MR_Paises_id",$data["paises"] ?? null);

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
        // $query = "DELETE FROM MR_Miembros WHERE id = :id";
        // $stmt = $this->connection->prepare($query);
        // $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        // $stmt->execute();
        $query = "UPDATE MR_Miembros SET activo = 0 WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        try {
            $query = "UPDATE MR_Login SET activo = 0 WHERE MR_Miembros_id = :id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            echo json_encode([
                "error" => "Error al eliminar el login del miembro: " . $e->getMessage()
            ]);
        }
    }
    public function updateMember($id, $new){
        // Get the current member data with the actual IDs, not just names
        $query = "SELECT id, nombre, apellidos, genero, MR_Universidades_id as universidad, 
                 MR_Estados_id as estado, MR_Paises_id as pais 
                 FROM MR_Miembros WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$current) throw new Exception('Miembro no encontrado');
        
        $query = "UPDATE MR_Miembros SET nombre=:newNombre, apellidos=:newApellido, genero=:newGenero, 
                 MR_Universidades_id=:newUniversidad, MR_Estados_id=:newEstado, MR_Paises_id=:newPais 
                 WHERE id=:id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":newNombre", $new["nombre"] ?? $current["nombre"]);
        $stmt->bindValue(":newApellido", $new["apellidos"] ?? $current["apellidos"]);
        $stmt->bindValue(":newGenero", $new["genero"] ?? $current["genero"]);
        $stmt->bindValue(":newUniversidad", $new["universidad"] ?? $current["universidad"]);
        $stmt->bindValue(":newEstado", $new["estado"] ?? $current["estado"]);
        $stmt->bindValue(":newPais", $new["pais"] ?? $current["pais"]);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        // Update email and/or password if either is provided
        if(isset($new["email"]) && isset($new["password"])) {
            $this->updateLogin($id, $new["email"], $new["password"]);
        } else if(isset($new["email"])) {
            $this->updateEmail($id, $new["email"]);
        } else if(isset($new["password"])) {
            $this->updatePassword($id, $new["password"]);
        }

        $stmt->execute();
        return $this->getMemberById($id);
    }

    public function updateLogin($id, $email, $password){
        $query = "UPDATE MR_Login SET email=:email, password_hash=:password WHERE MR_Miembros_id=:id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":email", $email);
        $stmt->bindValue(":password", $password);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    public function updateEmail($id, $email){
        $query = "UPDATE MR_Login SET email=:email WHERE MR_Miembros_id=:id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":email", $email);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    public function updatePassword($id, $password){
        $query = "UPDATE MR_Login SET password_hash=:password WHERE MR_Miembros_id=:id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":password", $password);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }


    public function getAllMembers(): array {
        $sql = "
            WITH SolicitudesOrdenadas AS (
                SELECT
                    u.id AS usuario_id,
                    CONCAT(u.nombre, ' ', u.apellidos) AS nombre_completo,
                    u.MR_TiposUsuario_id AS rol,
                    m.nombre AS membresia,
                    s.fecha_solicitud,
                    s.estado,
                    ROW_NUMBER() OVER (
                        PARTITION BY u.id
                        ORDER BY
                            CASE
                                WHEN s.estado = 'Aprobado' THEN 1
                                WHEN s.estado = 'Pendiente' THEN 2
                                WHEN s.estado = 'Rechazado' THEN 3
                                ELSE 4
                            END,
                            s.fecha_solicitud DESC
                    ) AS rn
                FROM MR_Miembros u
                LEFT JOIN MR_SolicitudesMembresia s ON u.id = s.MR_Miembros_id
                LEFT JOIN MR_Membresias m ON s.MR_Membresias_id = m.id
                WHERE u.activo = 1
            )
            SELECT usuario_id, nombre_completo, rol, membresia, fecha_solicitud, estado
            FROM SolicitudesOrdenadas
            WHERE rn = 1
            ORDER BY usuario_id;";
        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un miembro por ID con la misma estructura detallada
    public function getMemberById(string $id, bool $includeSensitive = false): ?array {
        if (empty($id) || !is_numeric($id)) {
            throw new Exception('ID inválido. Debe ser un número.');
        }
        
        $sql = "
            SELECT 
                MR_Miembros.id,
                " . ($includeSensitive ? "MR_Miembros.nombre, MR_Miembros.apellidos," : "") . "
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
                MR_Login.ultimo_acceso,
                MR_ArchivosMiembros.cv AS cv
            FROM MR_Miembros
            LEFT JOIN MR_Universidades ON MR_Miembros.MR_Universidades_id = MR_Universidades.id
            LEFT JOIN MR_Estados ON MR_Miembros.MR_Estados_id = MR_Estados.id
            LEFT JOIN MR_Paises ON MR_Miembros.MR_Paises_id = MR_Paises.id
            LEFT JOIN MR_EstatusMiembros ON MR_Miembros.MR_EstatusMiembros_id = MR_EstatusMiembros.id
            LEFT JOIN MR_TiposUsuario ON MR_Miembros.MR_TiposUsuario_id = MR_TiposUsuario.id
            LEFT JOIN MR_Login ON MR_Miembros.id = MR_Login.MR_Miembros_id
            LEFT JOIN MR_ArchivosMiembros ON MR_Miembros.id = MR_ArchivosMiembros.MR_Miembros_id
            WHERE MR_Miembros.id = :id AND MR_Miembros.activo = 1;
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$member) {
            throw new Exception('No se encontró un miembro con el ID proporcionado.');
        }
    
        if (!$includeSensitive) {
            unset($member['nombre']);
            unset($member['apellidos']);
        }
    
        return $member;
    }

    public function changeRole(string $id, array $data): bool {
        if (empty($id) || !is_numeric($id)) {
            throw new Exception('ID inválido. Debe ser un número.');
        }
        
        $query = "UPDATE MR_Miembros SET MR_TiposUsuario_id = :newRole WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":newRole", $data['role'], PDO::PARAM_INT);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
}
?>


