<?php 
global $connection;


spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . "/../src/Config/",
        __DIR__ . "/../src/Controllers/",
        __DIR__ . "/../src/Services/",
        __DIR__ . "/../src/Models/",
        __DIR__ . "/../src/Middleware/",
        __DIR__ . "/../src/Helpers/"
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
$configPath = __DIR__ . '/../src/Config/config.php';
if (!file_exists($configPath)) {
    die("Error: El archivo de configuración no existe en la ruta esperada.");
}
include_once $configPath;

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
$auth_middleware = new AuthMiddleware($jwt, ['login']);

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
        echo json_encode(["php_version" => phpversion()]);
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

    case 'investigationLine':
        echo json_encode(["php_version" => phpversion()]);
        break;

    //Ruta para solicitar membresias
    case "solicitarMembresia":
        $service = new MembershipApplicationService($dbConnection);
        $mailerService = new MailerService();
        $notificationService = new MembershipNotificationService($dbConnection);
        $controller = new MembershipApplicationController($service, $mailerService, $notificationService);
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
        $mailerService = new MailerService();
        $miembrosService = new MiembrosService($dbConnection);
        $miembrosController = new MiembrosController($miembrosService, $mailerService);
    
        $data = $_POST;
        if (empty($data)) {
            $data = (array) json_decode(file_get_contents("php://input"), true);
        }
    
        try {
            $response = $miembrosController->handleRequest($_SERVER, $id, $data);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'error' => $e->getMessage(),
                'success' => false
            ]);
        }
        break;
        // Verificar usuario por email
    case "verify":
        $mailerService = new MailerService();
        $miembrosService = new MiembrosService($dbConnection);
        $miembrosController = new MiembrosController($miembrosService, $mailerService);
        $data = (array) json_decode(file_get_contents("php://input"), true);
        try {
            $response = $miembrosController->verifyByEmail($data);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;

    // Reenviar código de verificación
    case "resend-code":
        $mailerService = new MailerService();
        $miembrosService = new MiembrosService($dbConnection);
        $miembrosController = new MiembrosController($miembrosService, $mailerService);
        $data = (array) json_decode(file_get_contents("php://input"), true);
        try {
            $response = $miembrosController->resendVerificationCode($data);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;

    case "universities":
        $universidadesService = new UniversidadesService($dbConnection);
        $universidadesController = new UniversidadesController($universidadesService);
        $data = $_POST;
        if (empty($data)){
            $data = (array) json_decode(file_get_contents("PHP://input"), true);
        }
        try {
            $universidadesController->handleRequest($_SERVER['REQUEST_METHOD'], $data);
        } catch (\Throwable $th) {
            http_response_code(400);
            echo json_encode(['error'=> $th->getMessage()]);
        }
        break;
    case "countries":
        $paisesService = new PaisesService($dbConnection);
        $paisesController = new PaisesController($paisesService);
        $data = $_POST;
        if (empty($data)){
            $data = (array) json_decode(file_get_contents("PHP://input"), true);
        }
        try {
            $paisesController->handleRequest($_SERVER['REQUEST_METHOD'], $data);
        } catch (\Throwable $th) {
            http_response_code(400);
            echo json_encode(['error'=> $th->getMessage()]);
        }
        break;
    case "states":
        $statesService = new StatesService($dbConnection);
        $statesController = new StatesController($statesService);
        $data = $_POST;
        if (empty($data)){
            $data = (array) json_decode(file_get_contents("PHP://input"), true);
        }

        if (empty($data)){
            $data = $_GET; 
        }

        try {
            $statesController->handleRequest($_SERVER['REQUEST_METHOD'], $data);
        } catch (\Throwable $th) {
            http_response_code(400);
            echo json_encode(['error'=> $th->getMessage()]);
        }
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
    
    case "actualizarEstadoMembresia":
        $membresiaUsuarioService = new MembresiaUsuarioService($dbConnection);
        $membresiaUsuarioController = new MembresiaUsuarioController($dbConnection);
        
        $data = $_POST;
        if (empty($data)) {
            $data = (array) json_decode(file_get_contents("php://input"), true);
        }
        
        try {
            $response = $membresiaUsuarioController->actualizarEstadoMembresia($id, $data);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
        break;

    case "uploadFile":
        $fileUploadService = new FileUploadService();
        $fileUploadController = new FileUploadController($fileUploadService);
        $fileUploadController->processRequest();
        break;

    case 'investigationLines':
        $service = new InvestigationLinesService($dbConnection);
        $data = $_POST;
        if (empty($data)) {
            $data = (array) json_decode(file_get_contents("php://input"), true);
        }
        $controller = new InvestigationLinesController($service, $data);
        try {
            $controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'memberInvestigation':
        $service = new MemberInvestigationService($dbConnection);
        $controller = new MemberInvestigationController($service);
        try {
            $controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
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

