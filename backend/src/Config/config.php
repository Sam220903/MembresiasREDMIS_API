<?php
    //Se definen los parametros para la conexion a la base de datos,en routes.php se hara su llamado
    namespace App\Config;
    use PDO;
    use PDOException;

    class Database {
        private static $host = "localhost";
        private static $dbname = "mr_db";
        private static $username = "mr_user";
        private static $password = "REDMIS";
        private static $pdo = null;

        public static function connect() {
            if (self::$pdo === null) {
                try {
                    self::$pdo = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8", self::$username, self::$password);
                    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    die("Error de conexión: " . $e->getMessage());
                }
            }
            return self::$pdo;
        }
    }