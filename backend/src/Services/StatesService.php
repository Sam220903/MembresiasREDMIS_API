<?php
class StatesService{
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllStates() {
        $sql = "SELECT id, nombre, MR_Paises_id as pais_id FROM MR_Estados";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $states = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $states[] = new State($row['id'], $row['nombre'], $row['pais_id']);
        }
        return $states;
    }

    public function getStatesPerCountry($country_id) {
        $sql = "SELECT id, nombre FROM MR_Estados WHERE MR_Paises_id = :country_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':country_id', $country_id, PDO::PARAM_INT);
        $stmt->execute();
        $states = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $states[] = new State($row['id'], $row['nombre'], (int)$row['pais_id']);
        }
        return $states;
    }

    public function getStateById($id): ?State {
        $sql = "SELECT id, nombre, MR_Paises_id as pais_id FROM MR_Estados WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new State($row['id'], $row['nombre'], (int)$row['pais_id']);
    }
    
    public function createState(State $state): bool {
        $sql = "INSERT INTO MR_Estados (nombre, MR_Paises_id) VALUES (:nombre, :pais_id)";
        $stmt = $this->conn->prepare($sql);
        
        // Obtenemos los datos a través de los getters del Modelo
        $name = $state->getName();
        $countryId = $state->getCountryId();
        
        $stmt->bindParam(':nombre', $name);
        $stmt->bindParam(':pais_id', $countryId);
        
        return $stmt->execute();
    }
    
    public function updateState(State $state): bool {
        $sql = "UPDATE MR_Estados SET nombre = :nombre, MR_Paises_id = :pais_id WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        $id = $state->getId();
        $name = $state->getName();
        $countryId = $state->getCountryId();

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $name);
        $stmt->bindParam(':pais_id', $countryId);

        return $stmt->execute();
    }
}