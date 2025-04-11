<?php
// Configuración de la base de datos usando variables de entorno
$connection = [
    "servername" => getenv('DB_HOST') ?: "db",
    "username" => getenv('DB_USER') ?: "mr_user",
    "password" => getenv('DB_PASSWORD') ?: "REDMIS",
    "dbname" => getenv('DB_NAME') ?: "mr_db"
];

// La configuración fallback se usa cuando las variables de entorno no están disponibles
// Configuración de la base de datos de PRODUCCIÓN
// $connection = [
//     "servername" => "lumacad.com.mx",
//     "username" => "lumacadc_membresias_v2",
//     "password" => "lumacadc_membresias_v2",
//     "dbname" => "lumacadc_membresias"
// ];