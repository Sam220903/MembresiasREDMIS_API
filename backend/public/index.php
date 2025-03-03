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




// Única conexión a la base de datos
$database = new Database($connection["servername"], $connection["username"], $connection["password"], $connection["dbname"]);
$dbConnection = $database->getConnection(); // Ensure you get the connection object

// Instancia de objetos para manejo de autorizaciones y roles
# $jwt = new JWT("1234567");
# $auth_middleware = new AuthMiddleware($jwt, $connection["login"]);
# $token_gateway = new TokenGateway($database);



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

/* 
// Manejo de autorización
try {
    $user_payload = $auth_middleware->handleRequest($route, $_SERVER["REQUEST_METHOD"], $tokenGateway, $id);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => $e->getMessage()]);
    exit();
}
*/

// Este switch se encarga de manejar las rutas de la API
switch ($route){
    // Ruta para obtener todos los usuarios
    case "test":
        echo json_encode(["message" => "Este es el endpoint de prueba"]);
        break;

    // Agregar más rutas aquí con su case:

    case "membresias":
        $membresiasService = new MembresiasService($dbConnection); // Pass the connection object
        $membresiasController = new MembresiaController($membresiasService);
        try {
            $response = $membresiasController->getMembresias($_SERVER);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(["message" => "Endpoint no encontrado"]);
        break;
}