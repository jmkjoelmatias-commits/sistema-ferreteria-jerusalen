<?php
/**
 * Conexión a Base de Datos PostgreSQL en PHP (3FN)
 */
class Conexion {
    public static function conectar() {
        $host = "localhost";
        $port = "5432";
        $dbname = "ferreteria_db";
        $user = "postgres";
        $password = "tico565mati";

        $connection_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
        $db = pg_connect($connection_string);
        
        if (!$db) {
            die("Error de conexión a PostgreSQL. Verifique los parámetros en Conexion.php");
        }
        
        return $db;
    }
}
?>
