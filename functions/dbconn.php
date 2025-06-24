<?php
/**
 * dbconn.php
 * Establece las conexiones a las bases de datos utilizando las variables
 * de entorno que ya han sido cargadas por bootstrap.php.
 */

// Conexión a la base de datos InOut
$conn = new mysqli(
    $_ENV['INOUT_DB_HOST'],
    $_ENV['INOUT_DB_USER'],
    $_ENV['INOUT_DB_PASS'],
    $_ENV['INOUT_DB_NAME']
);
$conn->set_charset('utf8mb4');

// Conexión a la base de datos Koha
$koha = new mysqli(
    $_ENV['KOHA_DB_HOST'],
    $_ENV['KOHA_DB_USER'],
    $_ENV['KOHA_DB_PASS'],
    $_ENV['KOHA_DB_NAME']
);
$koha->set_charset('utf8mb4');


// --- FUNCIONES AUXILIARES ---

/**
 * Devuelve la conexión principal a la base de datos.
 * La variable $conn es global dentro del scope de los archivos incluidos.
 * @return mysqli
 */
function get_db_connection(): mysqli
{
    global $conn;
    return $conn;
}

/**
 * Sanitiza una cadena de texto para evitar inyección SQL.
 * @param mysqli $connection
 * @param string $str
 * @return string
 */
function sanitize(mysqli $connection, string $str): string
{
    return $connection->real_escape_string($str);
}

