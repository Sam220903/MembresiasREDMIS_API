<?php
class MemberInvestigationService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getByMember(int $memberId): array {
        $sql = "SELECT mmi.id, mm.id as member_id, 
                       CONCAT(mm.nombre, ' ', mm.apellidos) as miembro,
                       mli.id as line_id, mli.nombre as linea,
                       mmi.fecha_inicio, mmi.fecha_fin
                FROM MR_MiembrosInvestigaciones mmi
                JOIN MR_Miembros mm ON mm.id = mmi.MR_Miembros_id
                JOIN MR_LineaInvestigaciones mli ON mli.id = mmi.MR_LineaInvestigaciones_id
                WHERE mm.id = :memberId
                ORDER BY mli.nombre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':memberId', $memberId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByLine(int $lineId): array {
        $sql = "SELECT mmi.id, mm.id as member_id,
                       CONCAT(mm.nombre, ' ', mm.apellidos) as miembro,
                       mli.id as line_id, mli.nombre as linea,
                       mmi.fecha_inicio, mmi.fecha_fin
                FROM MR_MiembrosInvestigaciones mmi
                JOIN MR_Miembros mm ON mm.id = mmi.MR_Miembros_id
                JOIN MR_LineaInvestigaciones mli ON mli.id = mmi.MR_LineaInvestigaciones_id
                WHERE mli.id = :lineId
                ORDER BY miembro";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':lineId', $lineId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assign(MemberInvestigation $memberInvestigation): bool {
        $sql = "INSERT INTO MR_MiembrosInvestigaciones 
                    (MR_Miembros_id, MR_LineaInvestigaciones_id, fecha_inicio)
                VALUES 
                    (:miembro_id, :linea_id, :fecha_inicio)";
        $stmt = $this->conn->prepare($sql);
        $memberId  = $memberInvestigation->getMemberId();
        $lineId    = $memberInvestigation->getLineId();
        $startDate = $memberInvestigation->getStartDate();
        $stmt->bindParam(':miembro_id',   $memberId,  PDO::PARAM_INT);
        $stmt->bindParam(':linea_id',     $lineId,    PDO::PARAM_INT);
        $stmt->bindParam(':fecha_inicio', $startDate);
        return $stmt->execute();
    }

    public function unassign(int $memberId, int $lineId): bool {
        $sql = "DELETE FROM MR_MiembrosInvestigaciones
                WHERE MR_Miembros_id = :miembro_id
                AND MR_LineaInvestigaciones_id = :linea_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':miembro_id', $memberId, PDO::PARAM_INT);
        $stmt->bindParam(':linea_id',   $lineId,   PDO::PARAM_INT);
        return $stmt->execute();
    }
}