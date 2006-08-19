<?php

require_once('PHPUnit2/Framework/TestCase.php');

require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

require_once('sabel/db/DBConnection.php');
require_once('sabel/db/InformationSchema.php');

require_once "sabel/db/Mapller.php";

require_once "sabel/db/Query.php";

require_once "sabel/db/driver/Interface.php";
require_once "sabel/db/driver/Pdo.php";
require_once "sabel/db/driver/Pgsql.php";

class Test_InformationSchema extends PHPUnit2_Framework_TestCase
{
  public function testUse()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'mysql:host=localhost;dbname=blog';
    $dbCon['user'] = 'root';
    $dbCon['pass'] = '';
    //$dbCon['dsn']  = 'pgsql:host=localhost;dbname=blog';
    //$dbCon['user'] = 'pgsql';
    //$dbCon['pass'] = 'pgsql';

    Sabel_Edo_DBConnection::addConnection('user', 'pdo', $dbCon);

    $is = new Edo_InformationSchema('user', 'blog');

    $tableOfAuthor = $is->getTable('author');
    $this->assertEquals(Edo_Type::INT, $tableOfAuthor->getColumnByName('id')->type);

    $tables = $is->getTables();
    foreach ($tables as $table) {
      $this->assertEquals(Edo_Type::INT, $table->getColumnByName('id')->type);

      foreach ($table->getColumns() as $column) {
        $this->assertTrue(is_object($column));
        if ($column->name == 'name') {
          $this->assertEquals(Edo_Type::STRING, $column->type);
        }
      }
    }
  }
  /*
  public function prototype()
  {
    $is = new Edo_InformationSchema();
    $userTable = $is->getTableByName('users');
    
    $tables = $is->getTables();
    foreach ($tables as $table) {
      $this->assertEquals(Edo_Type::INT, $table->getAutoIncrementColummn()->type);
      $this->assertEquals(Edo_Type::INT, $table->getColumnByBame('id')->type);
      $this->assertEquals('id', $table->getAutoIncrementColummn()->name);
      
      foreach ($table->getColumns() as $column) {
        if ($column->name == 'id') {
          $this->assertTrue($column->isAutoIncrement());
        }
        
        $this->assertEquals(Edo_Type::VARCHAR, $column->type);
        
        if ($column->name == 'age' && $column->hasDefaultValue()) {
          $this->assertEquals(0, $column->getDefaultValue());
        }
        
        if ($column->type == Edo_Type::STRING) {
          $this->assertTrue(is_int($column->length));
        }
        
        if ($column->type == Edo_Type::INT) {
          $this->assertTrue(is_int($column->min));
          $this->assertTrue(is_int($column->max));
        }
        
        // @todo how to use blob type.
      }
    }
  }
  */
}
