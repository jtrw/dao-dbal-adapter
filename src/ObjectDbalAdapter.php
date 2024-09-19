<?php

namespace Jtrw\DAO;

use Doctrine\DBAL\Connection;
use Jtrw\DAO\Driver\ObjectDriverInterface;
use Jtrw\DAO\Exceptions\DatabaseException;
use Jtrw\DAO\ObjectAdapter;
use Jtrw\DAO\ValueObject\ArrayLiteral;
use Jtrw\DAO\ValueObject\StringLiteral;
use Jtrw\DAO\ValueObject\ValueObjectInterface;

class ObjectDbalAdapter extends ObjectAdapter
{
    protected Connection $db;
    
    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->driver = $this->createDriverInstance();
    }
    
    private function createDriverInstance(): ObjectDriverInterface
    {
        $type = $this->getDatabaseType();
        
        $className = ucfirst($type).'ObjectDriver';
        $className = "\Jtrw\DAO\Driver\\".$className;
        if (!class_exists($className)) {
            throw new DatabaseException("Driver Not Found");
        }
        
        return new $className($this->db->getNativeConnection());
    }
    
    
    public function quote(string $obj, int $type = 0): string
    {
        return $this->db->quote($obj);
    }
    
    public function getRow(string $sql): ValueObjectInterface
    {
        $result = $this->db->fetchAssociative($sql);
        
        if (!$result) {
            $result = [];
        }
        
        return new ArrayLiteral($result);
    }
    
    public function getAll(string $sql): ValueObjectInterface
    {
        $result = $this->db->fetchAllAssociative($sql);
        
        if (!$result) {
            $result = [];
        }
        
        return new ArrayLiteral($result);
    }
    
    public function getCol(string $sql): ValueObjectInterface
    {
        $result = $this->db->fetchFirstColumn($sql);
        
        if (!$result) {
            $result = [];
        }
        
        return new ArrayLiteral($result);
    }
    
    public function getOne(string $sql): ValueObjectInterface
    {
        $result = $this->db->fetchOne($sql) ?? '';
        
        return new StringLiteral($result);
    }
    
    public function getAssoc(string $sql): ValueObjectInterface
    {
        $result = [];
        $res = $this->db->fetchAllAssociative($sql);
        foreach ($res as $key => $row) {
            $val = array_shift($row);
            if (count($row) === 1) {
                $row = array_shift($row);
            }
            $result[$val] = $row;
        }
        
        return new ArrayLiteral($result);
    }
    
    public function begin(bool $isolationLevel = false): void
    {
        $this->db->beginTransaction();
    }
    
    public function commit(): void
    {
        $this->db->commit();
    }
    
    public function rollback()
    {
        $this->db->rollBack();
    }
    
    public function query(string $sql): int
    {
        return $this->db->executeQuery($sql)->rowCount();
    }
    
    public function getInsertID(): int
    {
        return $this->db->lastInsertId();
    }
    
    public function getDatabaseType(): string
    {
        return $this->db->getNativeConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }
}
