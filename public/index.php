<?php

include_once __DIR__.'/../vendor/autoload.php';

$config = [
    'host' => 'clickhouse',
    'port' => '8123',
    'username' => 'default',
    'password' => 'password123'
];
$db = new ClickHouseDB\Client($config);

$db->database('default');
//$db->setTimeout(1);       // 10 seconds
//$db->setConnectTimeOut(5); // 5 seconds
//$db->ping(true); // if can`t connect throw exception

print_r($db->showTables());
