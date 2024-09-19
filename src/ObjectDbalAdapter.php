<?php

namespace Jtrw\DAO;

use Doctrine\DBAL\Connection;
use Jtrw\DAO\Driver\ObjectDriverInterface;
use Jtrw\DAO\Exceptions\DatabaseException;
use Jtrw\DAO\ObjectAdapter;
use Jtrw\DAO\ValueObject\ArrayLiteral;
use Jtrw\DAO\ValueObject\StringLiteral;
use Jtrw\DAO\ValueObject\ValueObjectInterface;
use Doctrine\DBAL\Exception;

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
        try {
            $result = $this->db->fetchAssociative($sql);
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
        
        if (!$result) {
            $result = [];
        }
        
        return new ArrayLiteral($result);
    }
    
    public function getAll(string $sql): ValueObjectInterface
    {
        try {
            $result = $this->db->fetchAllAssociative($sql);
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
        
        if (!$result) {
            $result = [];
        }
        
        return new ArrayLiteral($result);
    }
    
    public function getCol(string $sql): ValueObjectInterface
    {
        try {
            $result = $this->db->fetchFirstColumn($sql);
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
        
        if (!$result) {
            $result = [];
        }
        
        return new ArrayLiteral($result);
    }
    
    public function getOne(string $sql): ValueObjectInterface
    {
        try {
            $result = $this->db->fetchOne($sql) ?? '';
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
        
        return new StringLiteral($result);
    }
    
    public function getAssoc(string $sql): ValueObjectInterface
    {
        $result = [];
        try {
            $res = $this->db->fetchAllAssociative($sql);
            foreach ($res as $key => $row) {
                $val = array_shift($row);
                if (count($row) === 1) {
                    $row = array_shift($row);
                }
                $result[$val] = $row;
            }
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
        
        return new ArrayLiteral($result);
    }
    
    public function begin(bool $isolationLevel = false): void
    {
        try {
            $this->db->beginTransaction();
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
    }
    
    public function commit(): void
    {
        try {
            $this->db->commit();
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
    }
    
    /**
     * @throws DatabaseException
     * @return mixed
     */
    public function rollback()
    {
        try {
            $this->db->rollBack();
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
    }
    
    public function query(string $sql): int
    {
        try {
            return $this->db->executeQuery($sql)->rowCount();
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
    }
    
    public function getInsertID(): int
    {
        try {
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
    }
    
    public function getDatabaseType(): string
    {
        try {
            return $this->db->getNativeConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
    }
}
