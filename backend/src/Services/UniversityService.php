<?php
class UniversityService{
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }
    public function getAllUniversities() {
        $sql = "SELECT id, nombre, MR_Paises_id as pais_id FROM MR_Universidades";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $universities = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $universities[] = new University($row['id'], $row['nombre'], (int)$row['pais_id']);
        }
        return $universities;
    }
    public function getUniversityById($id): ?University {
        $sql = "SELECT id, nombre, MR_Paises_id as pais_id FROM MR_Universidades WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new University($row['id'], $row['nombre'], (int)$row['pais_id']);
    }
    public function createUniversity(University $university): bool {
        $sql = "INSERT INTO MR_Universidades (nombre, MR_Paises_id) VALUES (:nombre, :pais_id)";
        $stmt = $this->conn->prepare($sql);

        $name = $university->getName();
        $countryId = $university->getCountryId();

        $stmt->bindParam(':nombre', $name);
        $stmt->bindParam(':pais_id', $countryId);

        return $stmt->execute();
    }
    public function updateUniversity(University $university): bool {
        $sql = "UPDATE MR_Universidades SET nombre = :nombre, MR_Paises_id = :pais_id WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        $id = $university->getId();
        $name = $university->getName();
        $countryId = $university->getCountryId();

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $name);
        $stmt->bindParam(':pais_id', $countryId);

        return $stmt->execute();
    }
}