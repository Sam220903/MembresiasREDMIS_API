<?php 
global $connection;


spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . "/../src/Config/",
        __DIR__ . "/../src/Controllers/",
        __DIR__ . "/../src/Services/",
        __DIR__ . "/../src/Models/",
        __DIR__ . "/../src/Middleware/",
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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Just exit with 200 OK status
    http_response_code(200);
    exit();
}

// Firma JWT, esta clave debe de ser una variable de entorno en producción
$jwt = new Jwt('1234567');

// Única conexión a la base de datos
$database = new Database($connection["servername"], $connection["username"], $connection["password"], $connection["dbname"]);
$dbConnection = $database->getConnection(); // Ensure you get the connection object

$token_gateway = new TokenService($database);

// Instancia de objetos para manejo de autorizaciones y roles
$auth_middleware = new AuthMiddleware($jwt, ['login', 'miembros', 'universidades', 'paises', 'estados']);

// Obtener la ruta y el ID desde la URL
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

// Manejo de autorización
try {
    $user_payload = $auth_middleware->handleRequest($route, $_SERVER["REQUEST_METHOD"], $token_gateway, $id);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => $e->getMessage()]);
    exit();
}

// Este switch se encarga de manejar las rutas de la API
switch ($route){
    // Ruta para obtener todos los usuarios
    case "test":
        echo json_encode(["message" => "Este es el endpoint de prueba"]);
        break;

    // Agregar más rutas aquí con su case:
    // Ruta de inicio de sesión
    case "login":
        $user_service = new UserService($database);
        $controller = new LoginController($user_service, $jwt, $token_gateway);
        $controller->processRequest($_SERVER["REQUEST_METHOD"]);
        break;

    // Ruta de cierre de sesión
    case 'logout':
        $controller = new LogoutController($token_gateway);
        $controller->processRequest($_SERVER["REQUEST_METHOD"]);
        break;

    //Ruta para solicitar membresias
    case "solicitarMembresia":
        $service = new MembershipApplicationService($dbConnection); //  Ahora recibe la conexión
        $controller = new MembershipApplicationController($service);
        $controller->registerMembership();
        break;

    // Ruta de aceptar una membresía
    case "aceptarMembresia":
        $membershipService = new MembershipService($dbConnection); // Ahora recibe la conexión
        $mailerService = new MailerService();
        $controller = new MembershipRequestController($membershipService, $mailerService);
        $controller->acceptMembershipRequest($id);
        break;

    // Ruta de rechazar una membresía
    case "rechazarMembresia":
        $membershipService = new MembershipService($dbConnection); //  Ahora recibe la conexión
        $mailerService = new MailerService();
        $controller = new MembershipRequestController($membershipService, $mailerService);
        $controller->rejectMembershipRequest($id);
        break;

    case "membresias":
        $membresiasService = new MembresiasService($dbConnection); // Pass the connection object
        $membresiasController = new MembresiasController($membresiasService);
        $data = $_POST;
        if (empty($data)){
            $data = (array) json_decode(file_get_contents("PHP://input"), true);
        }

        try {
            $response = $membresiasController->handleRequest($_SERVER, $id, $data);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case "solicitudesMembresias":
        $solicitudesMembresiasService = new SolicitudesMembresiasService($dbConnection); // Pass the connection object
        $solicitudesMembresiasController = new SolicitudesMembresiasController($solicitudesMembresiasService);
        try {
            $solicitudesMembresiasController->processRequest($_SERVER['REQUEST_METHOD'], $id);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case "statistics":
        $service = new StatisticsService($database);
        $controller = new StatisticsController($service);  // Aumentar payload para autorización 
        $controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
        break;

    case "miembros": 
        $miembrosService = new MiembrosService($dbConnection);
        $miembrosController = new MiembrosController($miembrosService);

        $data = $_POST;
        if (empty($data)){
            $data = (array) json_decode(file_get_contents("PHP://input"), true);
        }
        try {
            $response = $miembrosController->handleRequest($_SERVER, $id, $data);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case "universidades":
        $universidadesService = new UniversidadesService($dbConnection);
        $universidadesController = new UniversidadesController($universidadesService);
        $universidadesController->listOfUniversidades();
        break;
    case "paises":
        $paisesService = new PaisesService($dbConnection);
        $paisesController = new PaisesController($paisesService);
        $paisesController->listOfPaises();
        break;
    case "estados":
        $estadosService = new EstadosService($dbConnection);
        $estadosController = new EstadosController($estadosService);
        $estadosController->listOfEstados();
        break;
        
    case "membresiaUsuario":
            $membresiaUsuarioService = new MembresiaUsuarioService($dbConnection);
            $membresiaUsuarioController = new MembresiaUsuarioController($dbConnection);
            
            $data = $_POST;
            if (empty($data)) {
                $data = (array) json_decode(file_get_contents("php://input"), true);
            }
            
            // Si hay un ID en la URL, es para obtener una membresía específica
            if ($id) {
                $data['usuarioId'] = $id; // Asignamos el ID de la URL como usuarioId
                try {
                    $response = $membresiaUsuarioController->obtenerMembresiaUsuario($data);
                    echo json_encode($response);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
            } 
            // Si no hay ID, es para listar todas las membresías de un usuario (necesita usuarioId en el body)
            else {
                try {
                    $response = $membresiaUsuarioController->listarMembresiasUsuario($data);
                    echo json_encode($response);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
            }
            break;

    case "cambiarRol":
        $miembrosService = new MiembrosService($dbConnection);
        $roleController = new RoleController($miembrosService);
        $data = $_POST;
        if (empty($data)){
            $data = (array) json_decode(file_get_contents("PHP://input"), true);
        }
        try {
            $response = $roleController->handleRequest($_SERVER, $id, $data);
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
