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

    public function createLine(InvestigationLine $line) : bool {
        $sql = "INSERT INTO MR_LineasInvestigaciones (nombre) VALUES (:nombre)";
        $stmt = $this->conn->prepare($sql);

        $name = $line->getName();

        $stmt->bindParam(':nombre', $name);

        return $stmt->execute();
    }

    public function assignLineToMember($lineId, $memberId, $startDate){
        $sql = "INSERT INTO MR_MiembrosInvestigaciones (MR_Miembros_id, MR_LineaInvestigaciones_id, fecha_inicio
                VALUES (:miembro_id, linea_id, fecha_inicio))";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':miembro_id', $memberId);
        $stmt->bindParam(':linea_id', $lineId);
        $stmt->bindParam(':fecha_inicio', $startDate);

        return $stmt->execute();
    }

    public function deleteLineToMember($lineId, $memberId){
        $sql = "DELETE FROM MR_MiembrosInvestigaciones 
                WHERE MR_Miembros_id = :miembro_id 
                AND MR_LineaInvestigaciones_id = :linea_id";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':miembro_id', $memberId);
        $stmt->bindParam(':linea_id', $lineId);

        return $stmt->execute();
    }
}