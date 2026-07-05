<?php
class InvestigationLinesService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllLines(): array {
        $sql = "SELECT * FROM MR_LineaInvestigaciones";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createLine(InvestigationLine $line): bool {
        $sql = "INSERT INTO MR_LineaInvestigaciones (nombre) VALUES (:nombre)";
        $stmt = $this->conn->prepare($sql);
        $name = $line->getName();
        $stmt->bindParam(':nombre', $name);
        return $stmt->execute();
    }
}