<?php
 

class MiembrosService{
    private $connection;
    public function __construct($connection){
        $this->connection=$connection;
    }
    public function postMiembro($data) {
        // Evitar registrar de nuevo un email ya existente. Se verifica antes
        // de insertar nada para no dejar un registro huérfano en MR_Miembros
        // si el email ya está en uso (lo que antes ocurría, ya que el
        // constraint UNIQUE de MR_Login se violaba después de haber creado
        // el miembro).
        $miembroExistente = $this->getMiembroByEmail($data["email"]);
        if ($miembroExistente) {
            throw new Exception('Ya existe una cuenta registrada con este correo electrónico.', 409);
        }

        // Generar código de verificación
        $codigo = $this->generarCodigoVerificacion();
        
        $query = "INSERT INTO MR_Miembros (nombre, apellidos, genero, codigo, verificado, MR_Universidades_id, MR_Estados_id, MR_Paises_id, MR_TiposUsuario_id) 
                  VALUES (:nombre, :apellidos, :genero, :codigo, 0, :MR_Universidades_id, :MR_Estados_id, :MR_Paises_id, 2)";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(":nombre", $data["nombre"]);
        $stmt->bindValue(":apellidos", $data["apellidos"]);
        $stmt->bindValue(":genero", $data["genero"]);
        $stmt->bindValue(":codigo", $codigo);  // Usar el código generado
        $stmt->bindValue(":MR_Universidades_id", $data["universidad"] ?? null);
        $stmt->bindValue(":MR_Estados_id", $data["estado"] ?? null);
        $stmt->bindValue(":MR_Paises_id", $data["paises"] ?? null);
    
        $stmt->execute();
        $miembroId = $this->connection->lastInsertId();

        try {
            $this->postLogin($miembroId, $data);
        } catch (PDOException $e) {
            // Red de seguridad ante una condición de carrera (dos registros
            // simultáneos con el mismo email pasando la verificación de
            // arriba casi al mismo tiempo). Se revierte el miembro recién
            // creado para no dejarlo huérfano sin credenciales.
            $this->connection->prepare("DELETE FROM MR_Miembros WHERE id = :id")
                ->execute([":id" => $miembroId]);

            if ($e->getCode() === '23000') {
                throw new Exception('Ya existe una cuenta registrada con este correo electrónico.', 409);
            }
            throw $e;
        }

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
            $hash = password_hash($new["password"], PASSWORD_BCRYPT);
            $this->updateLogin($id, $new["email"], $hash);
        } else if(isset($new["email"])) {
            $this->updateEmail($id, $new["email"]);
        } else if(isset($new["password"])) {
            $hash = password_hash($new["password"], PASSWORD_BCRYPT);
            $this->updatePassword($id, $hash);
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
        $sql = " SELECT u.id AS user_id, CONCAT(u.nombre, ' ', u.apellidos) AS name,
                    u.MR_TiposUsuario_id AS role, l.email
                    FROM MR_Miembros u
                    LEFT JOIN mr_db.MR_Login l ON u.id = l.MR_Miembros_id";
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
                MR_ArchivosMiembros.cv AS cv
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
            'ultimo_acceso' => $member['ultimo_acceso']
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