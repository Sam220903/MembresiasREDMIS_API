<?php
global $connection;

spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . "/../src/Config/",
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

$database = new Database($connection["servername"], $connection["username"], $connection["password"], $connection["dbname"]);
$conn = $database->getConnection();

// Get the full URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Split the path into parts
$parts = explode('/', trim($path, '/'));
// Get the last element and the one before it
$lastIndex = count($parts) - 1;
$route = $parts[$lastIndex - 1] ?? null;
$id = $parts[$lastIndex] ?? null;

switch($route) {
    case 'login':
        // Lee los datos del cuerpo de la solicitud
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'];
        $password = $data['password'];
        $auth_controller = new AuthController($conn);

        
        // Intenta iniciar sesión con las credenciales proporcionadas
        $token = $auth_controller->login($email, $password);
        if ($token) {
            // Si el inicio de sesión es exitoso, devuelve el token en formato JSON
            echo json_encode(['token' => $token]);
        } else {
            // Si las credenciales son inválidas, devuelve un mensaje de error y código 401
            http_response_code(401);
            echo json_encode(['message' => 'Credenciales Invalidas']);
        }
        break;

    case 'logout':
        // Lee los datos del cuerpo de la solicitud
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'];
        $auth_controller = new AuthController($conn);
        
        // Intenta cerrar sesión con el token proporcionado
        if ($auth_controller->logout($token)) {
            // Si el cierre de sesión es exitoso, devuelve un mensaje de confirmación
            echo json_encode(['message' => 'Logged out']);
        } else {
            // Si el token es inválido, devuelve un mensaje de error y código 400
            http_response_code(400);
            echo json_encode(['message' => 'Invalid token']);
        }
        break;

    default:
        // Si la acción solicitada no es reconocida, devuelve un mensaje de error y código 404
        http_response_code(404);
        echo json_encode(['message' => 'Action not found']);
        break;
        
        
}