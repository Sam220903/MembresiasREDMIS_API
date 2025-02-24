<?php

class StatisticsService{
    //Declaración de variables de la clase
    private PDO $conn;

    // Constructor (recibe objeto de base de datos)
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }
    
    // Función para obtener las estadísticas sobre miembros y membresias
    public function getStatistics(){

        // Al ser una consulta de selección, no es necesario hacer una preparación previa
        $sql = "SELECT * FROM MR_Membresias;";
        $result = $this->conn->query($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}