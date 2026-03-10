<?php declare(strict_types=1);

namespace StockOutAnalyzer\Core\Framework\ClickHouse;

use ClickHouseDB\Client;

class ClickHouseFactory
{
    public static function createClient(
        string $host,
        string $port,
        string $user,
        string $password,
        string $db
    ): Client {
        $config = [
            'host' => $host,
            'port' => $port,
            'username' => $user,
            'password' => $password,
            'dbname' => $db
        ];

        return new Client($config);
    }
}
