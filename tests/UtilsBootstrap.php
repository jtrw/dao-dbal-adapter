<?php
namespace Jtrw\DAO\Tests;

use Jtrw\DAO\DataAccessObjectInterface;

class UtilsBootstrap
{
    private DataAccessObjectInterface $object;
    
    public function __construct(DataAccessObjectInterface $object)
    {
        $this->object = $object;
    }
    
    public function createTables()
    {
        $sql = "CREATE OR REPLACE TABLE test
            (
                id UInt64,
                updated_at DateTime DEFAULT now(),
                updated_at_date Date DEFAULT toDate(updated_at)
            )
            ENGINE = MergeTree
            ORDER BY id;";
        $this->object->query($sql);
    }
}
