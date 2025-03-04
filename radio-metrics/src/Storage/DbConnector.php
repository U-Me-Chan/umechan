<?php

namespace Ridouchire\RadioMetrics\Storage;

use Medoo\Medoo;

class DbConnector
{
    private static ?Medoo $conn = null;

    public static function getConnection(string $database, string $hostname, string $username, string $password): Medoo
    {
        if (null === self::$conn) {
            self::$conn = new Medoo([
                'database_type' => 'mysql',
                'database_name' => $database,
                'server'        => $hostname,
                'username'      => $username,
                'password'      => $password,
                'charset'       => 'utf8mb4',
                'collation'     => 'utf8mb4_unicode_ci'
            ]);
        }

        return self::$conn;
    }
}
