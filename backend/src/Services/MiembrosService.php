<?php
 

class MiembrosService{
    private $connection;
    public function __construct($connection){
        $this->connection=$connection;
    }
    public function postMiembro($data) {
        // Generar código de verificación
        $codigo = $this->generarCodigoVerificacion();

        // El registro público siempre crea miembros sin verificar y con el tipo de usuario 2 (miembro estándar)
        $member = new Member(
            null,
            $data["nombre"],
            $data["apellidos"],
            $data["genero"],
            $data["universidad"] ?? null,
            $data["estado"] ?? null,
            $data["paises"] ?? null,
            null,
            2,
            $codigo,
            false,
            true
        );

        $query = "INSERT INTO MR_Miembros (nombre, apellidos, genero, codigo, verificado, MR_Universidades_id, MR_Estados_id, MR_Paises_id, MR_TiposUsuario_id) 
                  VALUES (:nombre, :apellidos, :genero, :codigo, 0, :MR_Universidades_id, :MR_Estados_id, :MR_Paises_id, :MR_TiposUsuario_id)";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":nombre", $member->getName());
        $stmt->bindValue(":apellidos", $member->getLastName());
        $stmt->bindValue(":genero", $member->getGender());
        $stmt->bindValue(":codigo", $member->getCode());
        $stmt->bindValue(":MR_Universidades_id", $member->getUniversityId());
        $stmt->bindValue(":MR_Estados_id", $member->getStateId());
        $stmt->bindValue(":MR_Paises_id", $member->getCountryId());
        $stmt->bindValue(":MR_TiposUsuario_id", $member->getUserTypeId());
    
        $stmt->execute();
        $miembroId = $this->connection->lastInsertId();
        $this->postLogin($miembroId, $data);
        
        // Devolver también el código generado para poder enviarlo por email
        return ["id" => $miembroId, "codigo" => $codigo];
    }

    public function verificarMiembro(int $id, string $codigo): bool {
        $query = "UPDATE MR_Miembros 
                 SET verificado = 1, codigo = NULL 
                 WHERE id = :id AND codigo = :codigo AND verificado = 0";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":codigo", $codigo);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    private function generarCodigoVerificacion(): string {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    

    public function postLogin($miembroId,$data){
        // Cifrado en el backend y con bcrypt
        $hash = password_hash($data["password"], PASSWORD_BCRYPT);

        $query="INSERT INTO MR_Login (MR_Miembros_id,email,password_hash) VALUES (:MR_Miembros_id,:email,:password_hash) ";
        $stmt=$this->connection->prepare($query);
        $stmt->bindValue(":MR_Miembros_id",$miembroId);
        $stmt->bindValue(":email",$data["email"]);
        $stmt->bindValue(":password_hash",$hash);

        $stmt->execute(); 
    }

    public function deleteMiembro($id) {
        $query = "DELETE FROM MR_Miembros WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
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

        $member = new Member(
            (int)$current["id"],
            $new["nombre"] ?? $current["nombre"],
            $new["apellidos"] ?? $current["apellidos"],
            $new["genero"] ?? $current["genero"],
            $new["universidad"] ?? $current["universidad"],
            $new["estado"] ?? $current["estado"],
            $new["pais"] ?? $current["pais"]
        );

        $query = "UPDATE MR_Miembros SET nombre=:newNombre, apellidos=:newApellido, genero=:newGenero, 
                 MR_Universidades_id=:newUniversidad, MR_Estados_id=:newEstado, MR_Paises_id=:newPais 
                 WHERE id=:id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":newNombre", $member->getName());
        $stmt->bindValue(":newApellido", $member->getLastName());
        $stmt->bindValue(":newGenero", $member->getGender());
        $stmt->bindValue(":newUniversidad", $member->getUniversityId());
        $stmt->bindValue(":newEstado", $member->getStateId());
        $stmt->bindValue(":newPais", $member->getCountryId());
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        // Update email and/or password if either is provided
        if(isset($new["email"]) && isset($new["password"])) {
            $hash = password_hash($new["password"], PASSWORD_BCRYPT);
            $this->updateLogin($id, $new["email"], $hash);
        } else if(isset($new["email"])) {
            $this->updateEmail($id, $new["email"]);
        } else if(isset($new["password"])) {
            $hash = password_hash($new["password"], PASSWORD_BCRYPT);
            $this->updatePassword($id, $hash);
        }

        $stmt->execute();

        // La línea de investigación vive en una tabla de relación aparte
        // (MR_MiembrosInvestigaciones), no en MR_Miembros: solo se toca si el
        // campo viene explícito en la petición (permite dejarla intacta en
        // ediciones que no la mencionan).
        if (array_key_exists('lineaInvestigacionId', $new)) {
            $this->setMemberInvestigationLine($id, $new['lineaInvestigacionId']);
        }

        return $this->getMemberById($id);
    }

    // Reemplaza la línea de investigación actual del miembro por la indicada
    // (o la elimina si $investigationLineId viene vacío/null).
    private function setMemberInvestigationLine($id, $investigationLineId){
        $delete = "DELETE FROM MR_MiembrosInvestigaciones WHERE MR_Miembros_id = :id";
        $stmt = $this->connection->prepare($delete);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if (empty($investigationLineId)) {
            return;
        }

        $insert = "INSERT INTO MR_MiembrosInvestigaciones (MR_Miembros_id, MR_LineaInvestigaciones_id) 
                   VALUES (:id, :lineaInvestigacionId)";
        $stmt = $this->connection->prepare($insert);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":lineaInvestigacionId", $investigationLineId, PDO::PARAM_INT);
        $stmt->execute();
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
    public function getMiembroByEmail($email) {
        $query = "SELECT m.id, m.nombre, m.codigo FROM MR_Miembros m 
                  JOIN MR_Login l ON m.id = l.MR_Miembros_id 
                  WHERE l.email = :email";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function reenviarCodigo($email) {
        $miembro = $this->getMiembroByEmail($email);
        if (!$miembro) {
            return false;
        }
        
        $codigo = $this->generarCodigoVerificacion();
        $query = "UPDATE MR_Miembros SET codigo = :codigo WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":codigo", $codigo);
        $stmt->bindValue(":id", $miembro['id'], PDO::PARAM_INT);
        $stmt->execute();
        
        return ["nombre" => $miembro["nombre"], "codigo" => $codigo];
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
                MR_Miembros.nombre AS nombre,
                " . ($includeSensitive ? "MR_Miembros.nombre, MR_Miembros.apellidos," : "") . "
                MR_Miembros.apellidos AS apellidos,
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
                MR_ArchivosMiembros.cv AS cv,
                (SELECT li.id FROM MR_MiembrosInvestigaciones mi
                    JOIN MR_LineaInvestigaciones li ON mi.MR_LineaInvestigaciones_id = li.id
                    WHERE mi.MR_Miembros_id = MR_Miembros.id
                    ORDER BY mi.id DESC LIMIT 1) AS linea_investigacion_id,
                (SELECT li.nombre FROM MR_MiembrosInvestigaciones mi
                    JOIN MR_LineaInvestigaciones li ON mi.MR_LineaInvestigaciones_id = li.id
                    WHERE mi.MR_Miembros_id = MR_Miembros.id
                    ORDER BY mi.id DESC LIMIT 1) AS linea_investigacion
            FROM MR_Miembros
            LEFT JOIN MR_Universidades ON MR_Miembros.MR_Universidades_id = MR_Universidades.id
            LEFT JOIN MR_Estados ON MR_Miembros.MR_Estados_id = MR_Estados.id
            LEFT JOIN MR_Paises ON MR_Miembros.MR_Paises_id = MR_Paises.id
            LEFT JOIN MR_EstatusMiembros ON MR_Miembros.MR_EstatusMiembros_id = MR_EstatusMiembros.id
            LEFT JOIN MR_TiposUsuario ON MR_Miembros.MR_TiposUsuario_id = MR_TiposUsuario.id
            LEFT JOIN MR_Login ON MR_Miembros.id = MR_Login.MR_Miembros_id
            LEFT JOIN MR_ArchivosMiembros ON MR_Miembros.id = MR_ArchivosMiembros.MR_Miembros_id
            WHERE MR_Miembros.id = :id;
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$member) {
            throw new Exception('No se encontró un miembro con el ID proporcionado.');
        }


        $response = [
            'id' => $member['id'],
            'nombre' => $member['nombre'],
            'apellidos' => $member['apellidos'],
            'genero' => $member['genero'],
            'fecha_registro' => $member['fecha_registro'],
            'ultima_actualizacion' => $member['ultima_actualizacion'],
            'universidad' => $member['universidad'],
            'estado' => $member['estado'],
            'pais' => $member['pais'],
            'estatus' => $member['estatus'],
            'tipo_usuario' => $member['tipo_usuario'],
            'email' => $member['email'],
            'ultimo_acceso' => $member['ultimo_acceso'],
            'lineaInvestigacionId' => $member['linea_investigacion_id'],
            'lineaInvestigacion' => $member['linea_investigacion']
        ];


        $filepath = '../src/pdfs/cvs/' . $member['cv'];

        if ($member) $response['cv_base64'] = base64_encode(file_get_contents($filepath));
    
        // if (!$includeSensitive) {
        //     unset($member['nombre']);
        //     unset($member['apellidos']);
        // }
    
        return $response;
    }

    public function changeRole(string $id, array $data): array {
        if (empty($id) || !is_numeric($id)) {
            throw new Exception('ID inválido. Debe ser un número.');
        }
        
        $query = "UPDATE MR_Miembros SET MR_TiposUsuario_id = :newRole WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":newRole", $data['role'], PDO::PARAM_INT);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        return [
            "id" => $id,
            "done" => $stmt->execute()
        ];
    }
    
}
?>
