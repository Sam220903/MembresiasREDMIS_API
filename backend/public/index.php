<?php

require __DIR__ . '/../src/Config/config.php';
require __DIR__ . '/../src/Routes/routes.php'; // Cargar rutas

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Conectar a la base de datos
try {
    $db = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}",
        $config['database']['username'],
        $config['database']['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la conexión hacia la base de datos']);
    exit;
}

// Obtener la URL y el método de la petición
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$route = "$method $path";

// Verificar si la ruta existe en `$routes`
if (isset($routes[$route])) {
    [$controller, $action] = $routes[$route];

    // Crear instancia del controlador dinámicamente
    $controllerClass = "App\\Controllers\\$controller";
    if (class_exists($controllerClass)) {
        $controllerInstance = new $controllerClass();
        echo $controllerInstance->$action();
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Controlador no encontrado']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada']);
}
?>