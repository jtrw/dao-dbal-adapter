<?php

namespace Jtrw\DAO;

use Jtrw\DAO\Exceptions\DatabaseException;
use Jtrw\DAO\ValueObject\ArrayLiteral;
use Jtrw\DAO\ValueObject\StringLiteral;
use Jtrw\DAO\ValueObject\ValueObjectInterface;

class ObjectDbalAdapter extends ObjectAdapter
{
    
    public function quote(string $obj, int $type = 0): string
    {
        // TODO: Implement quote() method.
    }
    
    public function getRow(string $sql): ValueObjectInterface
    {
        // TODO: Implement getRow() method.
    }
    
    public function getAll(string $sql): ValueObjectInterface
    {
        // TODO: Implement getAll() method.
    }
    
    public function getCol(string $sql): ValueObjectInterface
    {
        // TODO: Implement getCol() method.
    }
    
    public function getOne(string $sql): ValueObjectInterface
    {
        // TODO: Implement getOne() method.
    }
    
    public function getAssoc(string $sql): ValueObjectInterface
    {
        // TODO: Implement getAssoc() method.
    }
    
    public function begin(bool $isolationLevel = false)
    {
        // TODO: Implement begin() method.
    }
    
    public function commit()
    {
        // TODO: Implement commit() method.
    }
    
    public function rollback()
    {
        // TODO: Implement rollback() method.
    }
    
    public function query(string $sql): int
    {
        // TODO: Implement query() method.
    }
    
    public function getInsertID(): int
    {
        // TODO: Implement getInsertID() method.
    }
    
    public function getDatabaseType(): string
    {
        // TODO: Implement getDatabaseType() method.
    }
}
