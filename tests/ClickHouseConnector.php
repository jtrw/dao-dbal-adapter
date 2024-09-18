<?php

namespace Jtrw\DAO\Tests;

use ClickHouseDB\Client;
use Jtrw\DAO\DataAccessObjectInterface;
use Jtrw\DAO\ObjectDbalAdapter;
use RuntimeException;

class ClickHouseConnector
{
    public const DRIVER_NAME = "ClickHouse";
    
    /**
     * @var DataAccessObjectInterface
     */
    public static DataAccessObjectInterface $db;
    
    private static Client $sourceConnection;
    
    
    public static function init()
    {
        static::$db = self::initClickHouse();
    }
    
    private static function initClickHouse(): DataAccessObjectInterface
    {
        // $dbName = getenv('MYSQL_DATABASE');
        // $dsn = "mysql:dbname=dao;port=3306;host=dao_mariadb";
        
        $db = new Client(static::getConnectionConfig());
        
        static::$sourceConnection = $db;
        
        return new ObjectDbalAdapter($db);
    }
    
    private static function getConnectionConfig(): array
    {
        return [
            'host'     => getenv('CLICKHOUSE_HOST'),
            'port'     => getenv('CLICKHOUSE_PORT'),
            'username' => getenv('CLICKHOUSE_USER'),
            'password' => getenv('CLICKHOUSE_PASSWORD')
        ];
    }
    
    public static function getInstance(): DataAccessObjectInterface
    {
        if (null !== static::$db) {
            return static::$db;
        }
        
        throw new RuntimeException("Driver Not be Initialized");
    }
    
    public static function getSourceConnection(): Client
    {
        if (null !== static::$sourceConnection) {
            return static::$sourceConnection;
        }
    
        throw new RuntimeException("Conncetion Not be Initialized");
    }
}
