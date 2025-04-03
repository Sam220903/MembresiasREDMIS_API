<?php

class StatisticsService{
    //Declaración de variables de la clase
    private $conn;

    // Constructor (recibe objeto de base de datos)
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }
    
    // Función para obtener las estadísticas sobre miembros y membresias
    public function getStatistics(){

        // Al ser una consulta de selección, no es necesario hacer una preparación previa
        $sql = "SELECT CONCAT(m.nombre, ' ', m.apellidos) AS nombre, m.genero, u.nombre AS universidad, e.nombre AS estado, p.nombre AS pais, em.nombre AS estatus, tu.nombre AS tipo_usuario
                FROM MR_Miembros m
                JOIN MR_Universidades u ON m.MR_Universidades_id = u.id
                JOIN MR_Estados e ON m.MR_Estados_id = e.id
                JOIN MR_Paises p ON m.MR_Paises_id = p.id
                JOIN MR_EstatusMiembros em ON m.MR_EstatusMiembros_id = em.id
                JOIN MR_TiposUsuario tu ON m.MR_TiposUsuario_id = tu.id;";
        $result = $this->conn->query($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}