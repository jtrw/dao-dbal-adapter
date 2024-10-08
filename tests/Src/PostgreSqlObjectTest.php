<?php


use Jtrw\DAO\DataAccessObjectInterface;
use Jtrw\DAO\Exceptions\DatabaseException;
use Jtrw\DAO\Tests\DbConnector;
use Jtrw\DAO\ValueObject\ValueObjectInterface;
use PHPUnit\Framework\Assert;

class PostgreSqlObjectTest extends \Jtrw\DAO\Tests\Src\AbstractTestObjectAdapter
{
    public function setUp(): void
    {
        $this->db = DbConnector::getInstance(DbConnector::DRIVER_PGSQL);
        \Jtrw\DAO\Tests\Src\AbstractTestObjectAdapter::setUp(); // TODO: Change the autogenerated stub
    }
    
    public function testGetDataBaseType()
    {
        Assert::assertEquals(DbConnector::DRIVER_PGSQL, $this->db->getDatabaseType());
    }
    
    public function testMatch()
    {
        Assert::assertTrue(true);
    }
    
    public function testDeleteTable()
    {
        $tableName = "test_".time();
        $sql = "CREATE TABLE {$tableName} (id serial NOT NULL)";
        $this->db->query($sql);
        
        $sqlSelect = "SELECT * FROM ".$tableName;
        
        $result = $this->db->select($sqlSelect, [], [], DataAccessObjectInterface::FETCH_ALL)->toNative();
        Assert::assertEmpty($result);
        
        $this->db->deleteTable($tableName);
        
        try {
            $this->db->select($sqlSelect, [], [], DataAccessObjectInterface::FETCH_ALL)->toNative();
            $this->fail('DatabaseException was not thrown');
        } catch (DatabaseException $exp) {
            $msg = sprintf("relation \"%s\" does not exist", $tableName);
            Assert::assertStringContainsString($msg, $exp->getMessage(), "Message Not Found");
        }
    }
    
    public function testGetTableIndexes()
    {
        $indexes = $this->db->getTableIndexes(static::TABLE_SETTINGS);
        Assert::assertNotEmpty($indexes[0]['table']);
        Assert::assertEquals($indexes[0]['table'], 'settings_pkey');
    }
    
    public function testInsertForUpdate()
    {
        $values = [
            'id_parent' => 0,
            'caption'   => 'test',
            'value'     => 'dataTest'
        ];
        try {
            $this->db->insert(static::TABLE_SETTINGS, $values, true);
            Assert::fail("DatabaseException was not thrown");
        } catch (DatabaseException $exp) {
            Assert::assertEquals($exp->getMessage(), "Method Insert Not Support Third Param For pgsql DB Type.");
        }
    }
}
