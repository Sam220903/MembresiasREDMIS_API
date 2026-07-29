<?php
class InvestigationLinesService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllLines(): array {
        $sql = "SELECT * FROM MR_LineaInvestigaciones WHERE activo = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createLine(InvestigationLine $line): ?int {
        $sql = "INSERT INTO MR_LineaInvestigaciones (nombre) VALUES (:nombre)";
        $stmt = $this->conn->prepare($sql);
        $name = $line->getName();
        $stmt->bindParam(':nombre', $name);

        if (!$stmt->execute()) {
            return null;
        }

        return (int) $this->conn->lastInsertId();
    }

    public function deleteLine(int $id): bool {
        $sql = "UPDATE MR_LineaInvestigaciones SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}