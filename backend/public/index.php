<?php
global $connection;

spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . "/../src/Config/",
        __DIR__ . "/../src/Controllers/",
        __DIR__ . "/../src/Services/",
        __DIR__ . "/../src/Models/",
        __DIR__ . "/../src/Middleware/"
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class . ".php";
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

include_once '../src/Config/header.php';
include_once '../src/config/config.php';

// Firma JWT, esta clave debe de ser una variable de entorno en producción
$jwt = new Jwt('123456');

// Única conexión a la base de datos
$database = new Database($connection["servername"], $connection["username"], $connection["password"], $connection["dbname"]);

$tokenGateway = new TokenService($database);

// Encuentra la ruta y el id en la URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', trim($path, '/'));
$lastIndex = count($parts) - 1;
$route = $parts[$lastIndex - 1] ?? null;
$id = $parts[$lastIndex] ?? null;

// Si el id no es numérico, entonces es una ruta
if (!is_numeric($id)) {
    $route = $id;
    $id = null;
}


// Este Switch se encarga de manejar las rutas de la API
switch ($route){
    // Ruta para obtener todos los usuarios
    case "test":
        echo json_encode(["message" => "Este es el endpoint de prueba"]);
        break;

    // Agregar más rutas aquí con su case:

    default:
        http_response_code(404);
        echo json_encode(["message" => "Endpoint no encontrado"]);
        break;
}