<?php
// db.php
class Database {
    private static $pdo = null;

    // Constructor privado para evitar la creación de instancias fuera de la clase
    private function __construct() {}

    // Método estático para obtener la única instancia de la conexión
    public static function getInstance() {
        if (self::$pdo === null) {
            try {
                $host = "localhost";
                $dbname = "pizzeria";  // Nombre de tu base de datos
                $username = "root";    // Usuario de la base de datos
                $password = "";        // Contraseña de la base de datos

                // Establecer la conexión PDO
                self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Error en la conexión: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>

