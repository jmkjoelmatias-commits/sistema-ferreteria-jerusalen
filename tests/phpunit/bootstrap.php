<?php
/**
 * Bootstrap de pruebas — usa pg_connect igual que tu Conexion.php real
 * ⚠️ Cambia solo el dbname a ferreteria_test para no tocar la BD real
 */
 
define('DB_HOST',     'localhost');
define('DB_PORT',     '5432');
define('DB_NAME',     'ferreteria_test');   // BD separada para pruebas
define('DB_USER',     'postgres');
define('DB_PASSWORD', 'tico565mati');
 
function getTestDB() {
    $conn = pg_connect(
        "host=" . DB_HOST .
        " port=" . DB_PORT .
        " dbname=" . DB_NAME .
        " user=" . DB_USER .
        " password=" . DB_PASSWORD
    );
 
    if (!$conn) {
        throw new RuntimeException("No se pudo conectar a la BD de pruebas. Verifica que ferreteria_test exista en PostgreSQL.");
    }
 
    return $conn;
}
 