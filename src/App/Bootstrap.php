<?php
namespace App;

use Dotenv\Dotenv;
use mysqli;
use RuntimeException;

class Bootstrap
{
    private static bool $booted = false;
    private static mysqli $db;
    private static mysqli $koha;

    public static function init(string $baseDir = __DIR__ . '/../..'): void
    {
        if (self::$booted) {
            return;
        }

        require_once $baseDir . '/vendor/autoload.php';

        // Load environment
        if (is_readable($baseDir.'/.env')) {
            $dotenv = Dotenv::createImmutable($baseDir);
            $dotenv->load();
        }

        $required = [
            'INOUT_DB_HOST', 'INOUT_DB_USER', 'INOUT_DB_PASS', 'INOUT_DB_NAME',
            'KOHA_DB_HOST', 'KOHA_DB_USER', 'KOHA_DB_PASS', 'KOHA_DB_NAME'
        ];
        foreach ($required as $var) {
            if (empty($_ENV[$var])) {
                throw new RuntimeException("Missing env var: $var");
            }
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        self::$db = new mysqli(
            $_ENV['INOUT_DB_HOST'],
            $_ENV['INOUT_DB_USER'],
            $_ENV['INOUT_DB_PASS'],
            $_ENV['INOUT_DB_NAME']
        );
        self::$db->set_charset('utf8mb4');
        $GLOBALS['conn'] = self::$db;

        self::$koha = new mysqli(
            $_ENV['KOHA_DB_HOST'],
            $_ENV['KOHA_DB_USER'],
            $_ENV['KOHA_DB_PASS'],
            $_ENV['KOHA_DB_NAME']
        );
        self::$koha->set_charset('utf8mb4');
        $GLOBALS['koha'] = self::$koha;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        self::$booted = true;
    }

    public static function db(): mysqli
    {
        return self::$db;
    }

    public static function koha(): mysqli
    {
        return self::$koha;
    }
}
