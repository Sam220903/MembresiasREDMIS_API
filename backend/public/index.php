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

// If the last element is not numeric, swap positions
if (!is_numeric($id)) {
    $route = $id;
    $id = null;
}

echo "Route: $route, ID: $id";

// Now $route will contain your endpoint and $id will contain the ID (if present)
// regardless of how many folder levels you have