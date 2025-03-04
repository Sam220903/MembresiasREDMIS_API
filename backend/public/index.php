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
$dbConnection=$database->getConnection();


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

        case "miembros": 
            $miembrosService = new MiembrosService($dbConnection);
            $miembrosController = new MiembrosController($miembrosService);

            $data=$_POST;
            if (empty($data)){
                $data= (array) json_decode(file_get_contents("PHP://input"),true);
            }

            try {
                $idUser=$miembrosController->handleRequest($_SERVER,$id,$data);
                if ($id){
                    echo json_encode(["message" => "El id ".$id." fue eliminado"]);


                } 
                else {
                    echo json_encode(["message" => "El id ".$idUser." fue insertado"]);

                }
                
            } catch (Exception $e) {
                echo json_encode(["error" => $e->getMessage()]);
            }
            break;

          

    default:
        http_response_code(404);
        echo json_encode(["message" => "Endpoint no encontrado"]);
        break;
}