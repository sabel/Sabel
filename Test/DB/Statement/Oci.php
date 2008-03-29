<?php

/**
 * testcase of sabel.db.oci.Statement
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Statement_Oci extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Statement_Oci");
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.oci",
                    "host"     => "127.0.0.1",
                    "user"     => "develop",
                    "password" => "develop",
                    "database" => "xe");
    
    Sabel_DB_Config::add("default", $params);
  }
  
  public function testQuoteIdentifier()
  {
    $stmt = new Sabel_DB_Oci_Statement();
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
  
  public function testBuildSelectOrderByQuery()
  {
    $stmt = Sabel_DB::createStatement("default");
    $stmt->type(Sabel_DB_Statement::SELECT);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo("student"));
    $stmt->constraints(array("order" => "id DESC"));
    $expected = 'SELECT "ID", "NAME" FROM "STUDENT" ORDER BY "ID" DESC';
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectOrderByQuery2()
  {
    $stmt = Sabel_DB::createStatement("default");
    $stmt->type(Sabel_DB_Statement::SELECT);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo("student"));
    $stmt->constraints(array("order" => "id DESC, name ASC"));
    $expected = 'SELECT "ID", "NAME" FROM "STUDENT" ORDER BY "ID" DESC, "NAME" ASC';
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testClose()
  {
    Sabel_DB_Metadata::clear();
    Sabel_DB_Connection::closeAll();
  }
}
