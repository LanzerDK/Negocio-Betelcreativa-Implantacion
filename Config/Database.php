<?php

// Define el espacio de nombres para esta clase, correspondiente a la carpeta Config
namespace BetelCreativa\Config;

use PDO;
use PDOException;
// Clase que almacena las constantes de conexión a la base de datos
class Database
{
    
    // Dirección del servidor de base de datos, se puede modificar, ya que se desarrolla en un entorno local se coloca eso
    const DB_SERVER = "localhost";

    // Nombre de la base de datos utilizada por la aplicación
    const DB_NAME = "BetelCreativa";

    // Usuario de MySQL con permisos para acceder a la base de datos
    const DB_USER = "root";

    // Contraseña del usuario de MySQL (vacía en este caso)
    const DB_PASSWORD = "";

    //Variable estática para guardar la conexión y no abrirla múltiples veces
    private static ?PDO $instance = null;
    
    //Crea o retorna la conexión única a la base de datos usando las constantes
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            //Armamos la cadena de conexión (DSN) usando tus constantes.
            try {
                $dsn = "mysql:host=" . self::DB_SERVER . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASSWORD, $options);
            } catch (PDOException $e) {
                // Si la conexión falla, responde inmediatamente en formato JSON para el JS
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error de conexión a la base de datos.'
                ]);
                exit;
            }
        }
        return self::$instance;
    }
}