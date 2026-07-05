<?php
// Configuración de la base de datos usando variables de entorno

$connection = [
    // "servername" => "127.0.0.1",
    "servername" => "redmis_db",
    "username" => "mr_user",
    "password" => "#Redmis1",
    "dbname" => "mr_db"
];


// La configuración fallback se usa cuando las variables de entorno no están disponibles
// Configuración de la base de datos de PRODUCCIÓN

// $connection = [
//     "servername" => "lumacad.com.mx",
//     "username" => "lumacadc_membresias_v2",
//     "password" => "lumacadc_membresias_v2",
//     "dbname" => "lumacadc_membresias"
// ];