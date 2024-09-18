<?php

namespace Jtrw\DAO\Tests\Src;

use Jtrw\DAO\DataAccessObjectInterface;
use Jtrw\DAO\Exceptions\DatabaseException;
use Jtrw\DAO\Tests\ClickHouseConnector;
use Jtrw\DAO\ValueObject\ValueObjectInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestObjectAdapter extends TestCase
{
    public const TABLE_SETTINGS = "settings";
    
    public DataAccessObjectInterface $db;
    
    abstract public function testGetDataBaseType();
    
    abstract public function testGetTableIndexes();
    
    abstract public function testInsertForUpdate();
    
    
    public function testInsert()
    {
        $values = [
            'id_parent' => 0,
            'caption'   => 'test',
            'value'     => 'dataTest'
        ];
        $idSetting = $this->db->insert(static::TABLE_SETTINGS, $values);
        Assert::assertIsInt($idSetting);
        
        $sql = "SELECT * FROM settings";
        $search = [
            'id' => $idSetting
        ];
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ROW);
        Assert::assertInstanceOf(ValueObjectInterface::class, $result);
        
        $resultData = $result->toNative();
        Assert::assertNotEmpty($resultData);
        Assert::assertEquals($values['value'], $resultData['value']);
    }
    
    public function testMassInsert()
    {
        $values = [
            [
                'id_parent' => 0,
                'caption'   => 'massTest1',
                'value'     => 'dataMassTest1'
            ],
            [
                'id_parent' => 0,
                'caption'   => 'massTest2',
                'value'     => 'dataMassTest2'
            ]
        
        ];
        $this->db->massInsert(static::TABLE_SETTINGS, $values);
        
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        $search = [
            'caption' => $values[1]['caption']
        ];
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ROW);
        Assert::assertInstanceOf(ValueObjectInterface::class, $result);
        
        $resultData = $result->toNative();
        Assert::assertNotEmpty($resultData);
        Assert::assertEquals($values[1]['value'], $resultData['value']);
    }
    
    public function testMassInsertInForeach()
    {
        $values = [
            [
                'id_parent' => 0,
                'caption'   => 'massTest3',
                'value'     => 'dataMassTest3'
            ],
            [
                'id_parent' => 0,
                'caption'   => 'massTest4',
                'value'     => 'dataMassTest4'
            ]
        
        ];
        $this->db->massInsert(static::TABLE_SETTINGS, $values, true);
        
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        $search = [
            'caption' => $values[1]['caption']
        ];
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ROW);
        Assert::assertInstanceOf(ValueObjectInterface::class, $result);
        
        $resultData = $result->toNative();
        Assert::assertNotEmpty($resultData);
        Assert::assertEquals($values[1]['value'], $resultData['value']);
    }
    
    public function testUpdate()
    {
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        
        $result = $this->db->select($sql, [], [], DataAccessObjectInterface::FETCH_ALL);
        Assert::assertInstanceOf(ValueObjectInterface::class, $result);
        
        $resultData = $result->toNative();
        
        Assert::assertNotEmpty($resultData[0]);
        $currentValue = $resultData[0];
        
        $values = [
            'value' => "NewValueWithTimeStamp".time()
        ];
        
        $search = [
            'id' => $currentValue['id']
        ];
        
        $result = $this->db->update(static::TABLE_SETTINGS, $values, $search);
        Assert::assertIsInt($result);
        
        $sql = "SELECT * FROM settings";
        
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ROW);
        Assert::assertInstanceOf(ValueObjectInterface::class, $result);
        
        $resultData = $result->toNative();
        Assert::assertNotEmpty($resultData);
        Assert::assertEquals($resultData['value'], $values['value']);
    }
    
    public function testDelete()
    {
        $values = [
            'id_parent' => 0,
            'caption'   => 'forDelete',
            'value'     => 'dataTest'
        ];
        $idSetting = $this->db->insert(static::TABLE_SETTINGS, $values);
        Assert::assertIsInt($idSetting);
        
        $this->removeSettingRow($idSetting);
        
        $search = [
            'id' => $idSetting
        ];
        
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ROW);
        
        Assert::assertEmpty($result->toNative());
    }
    
    public function testAssoc()
    {
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        
        $fetchAssocObject = $this->db->select($sql, [], [], DataAccessObjectInterface::FETCH_ASSOC);
        Assert::assertInstanceOf(ValueObjectInterface::class, $fetchAssocObject);
        
        $fetchAllObject = $this->db->select($sql, [], [], DataAccessObjectInterface::FETCH_ALL);
        Assert::assertInstanceOf(ValueObjectInterface::class, $fetchAllObject);
        
        $assocData = $fetchAssocObject->toNative();
        $allData = $fetchAllObject->toNative();
        Assert::assertNotEmpty($allData[0]);
        
        Assert::assertEquals($assocData[$allData[0]['id']]['value'], $allData[0]['value']);
    }
    
    public function testGetTables()
    {
        Assert::assertSame(
            $this->db->getTables(),
            [
                "settings",
                "site_contents",
                "site_contents2settings"
            ]
        );
    }
    
    public function testSuccessTransactions()
    {
        $this->db->begin();
        
        $values = [
            'id_parent' => 0,
            'caption'   => 'TRANSACTION_BEGIN',
            'value'     => 'dataTest'
        ];
        $this->db->insert(static::TABLE_SETTINGS, $values);
        
        $values['caption'] = "TRANSACTION2_BEGIN";
        
        $idSetting = $this->db->insert(static::TABLE_SETTINGS, $values);
        
        $this->db->commit();
        
        Assert::assertNotEmpty($idSetting);
        $search = [
            'id' => $idSetting
        ];
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ROW);
        $resultData = $result->toNative();
        Assert::assertNotEmpty($resultData);
        Assert::assertEquals($resultData['id'], $idSetting);
    }
    
    public function testRollbackTransactions()
    {
        $idSetting = 0;
        try {
            $this->db->begin();
            
            $values = [
                'id_parent' => 0,
                'caption'   => 'TRANSACTION_BEGIN',
                'value'     => 'dataTest'
            ];
            $idSetting = $this->db->insert(static::TABLE_SETTINGS, $values);
            
            $values = [
                'failed_field' => 0,
            ];
            $this->db->insert(static::TABLE_SETTINGS, $values);
            
            $this->db->commit();
        } catch (DatabaseException $exp) {
            $this->db->rollback();
        }
        
        Assert::assertNotEmpty($idSetting);
        $search = [
            'id' => $idSetting
        ];
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ROW);
        Assert::assertEmpty($result->toNative());
    }
    
    public function testSelectIn()
    {
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        $search = [
            'id&IN' => [1, 2, 3],
            'id&NOT IN' => [4]
        ];
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ALL);
        $resultData = $result->toNative();
        Assert::assertNotEmpty($resultData['0']['id']);
        Assert::assertEquals($resultData['0']['id'], 1);
        
        Assert::assertNotEmpty($resultData['1']['id']);
        Assert::assertEquals($resultData['1']['id'], 2);
        
        Assert::assertNotEmpty($resultData['2']['id']);
        Assert::assertEquals($resultData['2']['id'], 3);
    }
    
    public function testSqlOr()
    {
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        $search = [
            "sql_or" => [
                [
                    "caption&IS NOT" =>NULL
                ],
                [
                    "id&!=" => 1
                ]
            ]
        ];
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ALL);
        Assert::assertNotEmpty($result->toNative());
    }
    
    public function testOrSql()
    {
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        $search = [
            "&or_sql" => ["id = 2", "id = 3"]
        ];
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ALL);
        $resultData = $result->toNative();
        Assert::assertCount(2, $resultData);
    }
    
    public function testSqlAnd()
    {
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        $search = [
            'sql_and' => [
                [
                    'sql_or' => [
                        [
                            'id' => 5,
                        ],
                        [
                            'caption&IS NOT' => 'NULL',
                        ],
                    ],
                ],
                [
                    'sql_or' => [
                        [
                            'id&IN' => [1, 2, 3],
                        ],
                        [
                            'caption&IS NOT' => 'NULL',
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ALL);
        $resultData = $result->toNative();
        // TODO: Fix this assert
        Assert::assertNotEmpty($resultData);
    }
    
    public function testOr()
    {
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        $search = [
            "id&or&<" => [
                2,
                [
                    'id' => 2
                ]
            ]
        ];
        $result = $this->db->select($sql, $search, ['id ASC'], DataAccessObjectInterface::FETCH_ALL);
        $resultData = $result->toNative();

        Assert::assertCount(2, $resultData);
        
        Assert::assertNotEmpty($resultData[0]['id']);
        Assert::assertEquals($resultData[0]['id'], 1);
    
        Assert::assertNotEmpty($resultData[1]['id']);
        Assert::assertEquals($resultData[1]['id'], 2);
    }
    
    public function testMatch()
    {
        $values = [
            'id_parent' => 0,
            'caption'   => 'maches',
            'value'     => 'Full text maches data text'
        ];
        $idSetting = $this->db->insert(static::TABLE_SETTINGS, $values);
        Assert::assertIsInt($idSetting);
        
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        $search = [
            "value&match" => "text data"
        ];
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ALL);
        $resultData = $result->toNative();
        
        Assert::assertNotEmpty($resultData[0]['value']);
        Assert::assertEquals($resultData[0]['value'], $values['value']);
        
        $this->removeSettingRow($idSetting);
    }
    
    public function testBetween()
    {
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        $search = [
            "id&between" => [1,3]
        ];
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ALL);
        Assert::assertInstanceOf(ValueObjectInterface::class, $result);
        $resultData = $result->toNative();
        Assert::assertCount(3, $resultData);
    }
    
    public function testSoundex()
    {
        $values = [
            'id_parent' => 0,
            'caption'   => 'Soundex',
            'value'     => 'Search for Soundex data text'
        ];
        $idSetting = $this->db->insert(static::TABLE_SETTINGS, $values);
        Assert::assertIsInt($idSetting);
        
        $sql = "SELECT * FROM ".static::TABLE_SETTINGS;
        $search = [
            "caption&soundex" => "Soundex"
        ];
    
        $result = $this->db->select($sql, $search, [], DataAccessObjectInterface::FETCH_ALL);
        $resultData = $result->toNative();
    
        Assert::assertNotEmpty($resultData[0]['value']);
        Assert::assertEquals($resultData[0]['value'], $values['value']);
    
        $this->removeSettingRow($idSetting);
    }
    
    public function testDeleteTable()
    {
        $tableName = "test_".time();
        $sql = "CREATE TABLE {$tableName} (id int unsigned not null)";
        $this->db->query($sql);
        
        $sqlSelect = "SELECT * FROM ".$tableName;
        
        $result = $this->db->select($sqlSelect, [], [], DataAccessObjectInterface::FETCH_ALL)->toNative();
        Assert::assertEmpty($result);
        
        $this->db->deleteTable($tableName);
    
        try {
            $this->db->select($sqlSelect, [], [], DataAccessObjectInterface::FETCH_ALL)->toNative();
            $this->fail('DatabaseException was not thrown');
        } catch (DatabaseException $exp) {
            $msg = sprintf(" Table 'dao.%s' doesn't", $tableName);
            Assert::assertEquals($exp->getQuery(), $sqlSelect);
            Assert::assertStringContainsString($msg, $exp->getMessage(), "Message Not Found");
        }
    }
    
    public function testSetForeignKeyChecks()
    {
        $this->db->setForeignKeyChecks(true);
    }
    
    private function removeSettingRow(int $id): void
    {
        $countRows = $this->db->delete(static::TABLE_SETTINGS, ['id' => $id]);
    
        Assert::assertEquals(1, $countRows);
    }
}
