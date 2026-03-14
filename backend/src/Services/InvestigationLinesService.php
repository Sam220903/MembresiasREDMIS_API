<?php

class InvestigationLinesService {
    private $conn;

     public function __construct($conn) {
        $this->conn = $conn;
    }


    public function getAllLines() {
        $sql = "SELECT * FROM MR_LineasInvestigaciones";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLinesByMember($memberId){
        $sql = "SELECT CONCAT(mm.nombre , ' ', mm.apellidos ) as miembro, mli.nombre, mmi.fecha_fin , mmi.fecha_inicio  FROM MR_Miembros mm 
                JOIN MR_MiembrosInvestigaciones mmi ON mm.id = mmi.MR_Miembros_id 
                JOIN MR_LineaInvestigaciones mli ON mli.id = mmi.MR_LineaInvestigaciones_id
                WHERE mm.id = :memberId
                ORDER BY miembro ;";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':memberId', $memberId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}