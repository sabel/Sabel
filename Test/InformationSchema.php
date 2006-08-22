<?php

require_once('PHPUnit2/Framework/TestCase.php');

require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

require_once('sabel/db/Connection.php');
require_once('sabel/db/InformationSchema.php');

require_once "sabel/db/Mapper.php";

require_once "sabel/db/query/Interface.php";
require_once "sabel/db/query/Factory.php";
require_once "sabel/db/driver/Interface.php";

require_once "sabel/db/driver/Pdo.php";
require_once "sabel/db/driver/Pgsql.php";

class Test_InformationSchema extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_InformationSchema");
  }
  
  public function testUse()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'mysql:host=localhost;dbname=blog';
    $dbCon['user'] = 'root';
    $dbCon['pass'] = '';
    //$dbCon['dsn']  = 'pgsql:host=localhost;dbname=blog';
    //$dbCon['user'] = 'pgsql';
    //$dbCon['pass'] = 'pgsql';

    Sabel_DB_Connection::addConnection('user', 'pdo', $dbCon);

    $is = new Sabel_DB_Schema('user', 'blog');

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
}
