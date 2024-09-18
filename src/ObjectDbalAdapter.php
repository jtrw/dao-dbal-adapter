<?php

namespace Jtrw\DAO;

use Doctrine\DBAL\Connection;
use Jtrw\DAO\Exceptions\DatabaseException;
use Jtrw\DAO\ValueObject\ArrayLiteral;
use Jtrw\DAO\ValueObject\StringLiteral;
use Jtrw\DAO\ValueObject\ValueObjectInterface;

class ObjectDbalAdapter extends ObjectAdapter
{
    protected Connection $db;
    public function __construct(Connection $db)
    {
        parent::__construct($db);
    }
    
    
    public function quote(string $obj, int $type = 0): string
    {
        $this->db->quote($obj);
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
        $result = $this->db->fetchOne($sql);
        
        if (!$result) {
            $result = [];
        }
        
        return new StringLiteral($result);
    }
    
    public function getAssoc(string $sql): ValueObjectInterface
    {
        $result = $this->db->fetchAssociative($sql);
        
        if (!$result) {
            $result = [];
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
        return $this->db->getDatabasePlatform()->getName();
    }
}
