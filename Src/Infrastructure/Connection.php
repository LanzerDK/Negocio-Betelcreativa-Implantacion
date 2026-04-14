<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use PDO;
use PDOException;


class Connection
{
    public static function connect()
    {

        try {
            //Conexion a la base
            $dns = "mysql:host=" . Database::DB_SERVER . ";dbname=" . Database::DB_NAME;
            $Connect = new PDO($dns, Database::DB_USER, Database::DB_PASSWORD);
            $Connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $Connect;
        } catch (PDOException $ex) {
            //Captura el mensaje 
            die($ex->getMessage());
        }
    }
}
