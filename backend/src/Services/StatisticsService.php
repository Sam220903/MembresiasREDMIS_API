<?php

class StatisticsService{
    //Declaración de variables de la clase
    private $conn;

    // Constructor (recibe objeto de base de datos)
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }
    
    // // Función para obtener las estadísticas sobre miembros y membresias
    // public function getStatistics(){

    //     // Al ser una consulta de selección, no es necesario hacer una preparación previa
    //     $sql = "SELECT CONCAT(m.nombre, ' ', m.apellidos) AS nombre, m.genero, u.nombre AS universidad, e.nombre AS estado, p.nombre AS pais, em.nombre AS estatus, tu.nombre AS tipo_usuario
    //             FROM MR_Miembros m
    //             JOIN MR_Universidades u ON m.MR_Universidades_id = u.id
    //             JOIN MR_Estados e ON m.MR_Estados_id = e.id
    //             JOIN MR_Paises p ON m.MR_Paises_id = p.id
    //             JOIN MR_EstatusMiembros em ON m.MR_EstatusMiembros_id = em.id
    //             JOIN MR_TiposUsuario tu ON m.MR_TiposUsuario_id = tu.id;";
    //     $result = $this->conn->query($sql);
    //     return $result->fetchAll(PDO::FETCH_ASSOC);
    // }


    private function getApplicationsPerStatus() {
        // Primer consulta: Conteo de solicitudes por estatus
        $sql = "SELECT s.estado AS membership_status, COUNT(*) AS total_applications
                FROM (SELECT 'APROBADA' AS estado UNION ALL 
                SELECT 'RECHAZADA' AS estado UNION ALL SELECT 'PENDIENTE' as estado) e
                LEFT JOIN MR_SolicitudesMembresia s ON s.estado = e.estado
                GROUP BY s.estado;";

        $result = $this->conn->query($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getMembershipsPerStatus(){
        // Segunda consulta: Conteo de membresias por estado
        $sql = "SELECT e.estado AS membership_status, COUNT(m.estado) AS total_memberships
                FROM ( SELECT 'ACTIVA' AS estado UNION ALL SELECT 'INACTIVA' AS estado) e
                LEFT JOIN MR_MiembrosMembresias m ON m.estado = e.estado
                GROUP BY e.estado;";

        $result = $this->conn->query($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getMembersPerCountry() {
        // Tercer consulta: Conteo de miembros (activos) por país
        $sql = "SELECT  p.nombre AS pais, COUNT(*) AS total_members
                FROM MR_Miembros m1 JOIN MR_MiembrosMembresias mm ON (m1.id = mm.MR_Miembros_id) 
                JOIN MR_Membresias m2 ON (m2.id = mm.MR_Membresias_id) 
                JOIN MR_Paises p ON (m1.MR_Paises_id = p.id)
                WHERE mm.estado = 'ACTIVA'
                GROUP BY p.nombre;";

        $result = $this->conn->query($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getMembersPerUniversity() {
        // Cuarta consulta: Conteo de miembros (activos) por universidad
        $sql = "SELECT  u.nombre AS universidad, COUNT(*) AS total_members
                FROM MR_Miembros m1 JOIN MR_MiembrosMembresias mm ON (m1.id = mm.MR_Miembros_id) 
                JOIN MR_Membresias m2 ON (m2.id = mm.MR_Membresias_id) 
                JOIN MR_Universidades u ON (m1.MR_Universidades_id = u.id)
                WHERE mm.estado = 'ACTIVA'
                GROUP BY u.nombre;";

        $result = $this->conn->query($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nueva función para retorno de estadísticas
    public function getStatistics(){
        
        $applications = $this->getApplicationsPerStatus();
        $memberships = $this->getMembershipsPerStatus();
        $membersPerCountry = $this->getMembersPerCountry();
        $membersPerUniversity = $this->getMembersPerUniversity();

        return array ( 
            "applications" => $applications,
            "memberships" => $memberships,
            "members_per_country" => $membersPerCountry,
            "members_per_university" => $membersPerUniversity
        );   
    }


}