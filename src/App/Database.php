<?php
namespace App;

use mysqli;

class Database
{
    public static function connection(): mysqli
    {
        Bootstrap::init(__DIR__ . '/..');
        return Bootstrap::db();
    }
}
