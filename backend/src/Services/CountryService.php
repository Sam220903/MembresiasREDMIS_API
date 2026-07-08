<?php
class CountryService{
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getAllCountries() {
        $sql = "SELECT id, nombre FROM MR_Paises";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $countries = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $countries[] = new Country($row['id'], $row['nombre']);
        }
        return $countries;
    }
    public function getCountryById($id): ?Country {
        $sql = "SELECT id, nombre FROM MR_Paises WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Country($row['id'], $row['nombre']);
    }
    public function createCountry(Country $country): bool {
        $sql = "INSERT INTO MR_Paises (nombre) VALUES (:nombre)";
        $stmt = $this->conn->prepare($sql);

        $name = $country->getName();

        $stmt->bindParam(':nombre', $name);

        return $stmt->execute();
    }
    public function updateCountry(Country $country): bool {
        $sql = "UPDATE MR_Paises SET nombre = :nombre WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        $id = $country->getId();
        $name = $country->getName();

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $name);

        return $stmt->execute();
    }
}