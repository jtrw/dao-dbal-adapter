<?php
require __DIR__."/../vendor/autoload.php";

use Jtrw\DAO\Tests\UtilsBootstrap;
use Jtrw\DAO\Tests\ClickHouseConnector;

ClickHouseConnector::init();

$creteTestTables = new UtilsBootstrap(ClickHouseConnector::getInstance());

$creteTestTables->createTables();

