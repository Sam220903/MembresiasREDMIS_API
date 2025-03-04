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

}


