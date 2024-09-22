<?php

namespace Jtrw\DAO\Tests;

use Doctrine\DBAL\DriverManager;
use Jtrw\DAO\DataAccessObject;
use Jtrw\DAO\DataAccessObjectInterface;
use Jtrw\DAO\ObjectDbalAdapter;
use RuntimeException;
use PDO;

class DbConnector
{
    public const DRIVER_MYSQL = "mysql";
    public const DRIVER_PGSQL = "pgsql";
    
    /**
     * @var DataAccessObjectInterface[]
     */
    public static array $db = [
        self::DRIVER_MYSQL => null,
        self::DRIVER_PGSQL => null
    ];
    
    public static array $sourcePDO = [
        self::DRIVER_MYSQL => null,
        self::DRIVER_PGSQL => null
    ];
    
    public static function init()
    {
        static::$db[static::DRIVER_MYSQL] = self::initMysql();
        //static::$db[static::DRIVER_PGSQL] = self::iniPgSql();
    }
    
    private static function initMysql(): DataAccessObjectInterface
    {
        $dbName = getenv('MYSQL_DATABASE');
        $dsn = "mysql:dbname=dao;port=3306;host=dao_mariadb";

        $db = new \PDO(
            $dsn,
            getenv('MYSQL_USER'),
            getenv('MYSQL_PASSWORD')
        );
    
        static::$sourcePDO[static::DRIVER_MYSQL] = $db;
        
        $params = [
            'driver' => 'pdo_mysql',
            'dbname' => $dbName,
            'user' => getenv('MYSQL_USER'),
            'password' => getenv('MYSQL_PASSWORD'),
            'host' => 'dao_mariadb',
            'port' => 3306
        ];
        $dbal = DriverManager::getConnection($params);

        return new ObjectDbalAdapter($dbal);
    }
    
    private static function iniPgSql()
    {
        $params = [
            'driver' => 'pdo_pgsql',
            'dbname' => 'dao',
            'user' => 'postgres_user',
            'password' => 'postgres_pass',
            'host' => 'dao_postgres',
            'port' => 5432
        ];
        $dbal = DriverManager::getConnection($params);

        return new ObjectDbalAdapter($dbal);
    }
    
    public static function getInstance(string $driver = self::DRIVER_MYSQL): DataAccessObjectInterface
    {
        if (null !== static::$db[$driver]) {
            return static::$db[$driver];
        }
        
        throw new RuntimeException("Driver Not be Initialized");
    }
    
    public static function getSourcePdo(string $driver = self::DRIVER_MYSQL): PDO
    {
        if (null !== static::$sourcePDO[$driver]) {
            return static::$sourcePDO[$driver];
        }
    
        throw new RuntimeException("PDO Not be Initialized");
    }
}
