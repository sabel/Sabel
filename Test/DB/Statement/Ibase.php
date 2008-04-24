<?php

/**
 * testcase of sabel.db.ibase.Statement
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Statement_Ibase extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Statement_Ibase");
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.ibase",
                    "host"     => "localhost",
                    "user"     => "develop",
                    "password" => "develop",
                    "database" => "/home/firebird/sdb_test.fdb");
    
    Sabel_DB_Config::add("default", $params);
  }
  
  public function testQuoteIdentifier()
  {
    $stmt = Sabel_DB::createStatement("default");
    $this->assertEquals('"FOO"', $stmt->quoteIdentifier("foo"));
    $this->assertEquals('"BAR"', $stmt->quoteIdentifier("bar"));
  }
  
  public function testBuildSelectQuery()
  {
    $stmt = Sabel_DB::createStatement("default");
    $stmt->type(Sabel_DB_Statement::SELECT);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo("student"));
    $expected = 'SELECT "ID", "NAME" FROM "STUDENT"';
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectWhereQuery()
  {
    $stmt = Sabel_DB::createStatement("default");
    $stmt->type(Sabel_DB_Statement::SELECT);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo("student"));
    $stmt->where('WHERE "ID" = 1');
    $expected = 'SELECT "ID", "NAME" FROM "STUDENT" WHERE "ID" = 1';
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testClose()
  {
    Sabel_DB_Metadata::clear();
    Sabel_DB_Connection::closeAll();
  }
}
