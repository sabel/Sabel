<?php

/**
 * testcase of sabel.db.mysql.Statement
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Statement_Mysql extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Statement_Mysql");
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.mysql",
                    "host"     => "127.0.0.1",
                    "user"     => "root",
                    "password" => "",
                    "database" => "sdb_test");
    
    Sabel_DB_Config::add("default", $params);
  }
  
  public function testQuoteIdentifier()
  {
    $stmt = new Sabel_DB_Mysql_Statement();
    $this->assertEquals("`foo`", $stmt->quoteIdentifier("foo"));
    $this->assertEquals("`bar`", $stmt->quoteIdentifier("bar"));
  }
  
  public function testBuildSelectQuery()
  {
    $stmt = new Sabel_DB_Mysql_Statement();
    $stmt->type(Sabel_DB_Statement::SELECT);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo("student"));
    $expected = "SELECT `id`, `name` FROM `student`";
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectWhereQuery()
  {
    $stmt = new Sabel_DB_Mysql_Statement();
    $stmt->type(Sabel_DB_Statement::SELECT);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo("student"));
    $stmt->where("WHERE `id` = 1");
    $expected = "SELECT `id`, `name` FROM `student` WHERE `id` = 1";
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectOrderByQuery()
  {
    $stmt = new Sabel_DB_Mysql_Statement();
    $stmt->type(Sabel_DB_Statement::SELECT);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo("student"));
    $stmt->constraints(array("order" => "id DESC"));
    $expected = "SELECT `id`, `name` FROM `student` ORDER BY `id` DESC";
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectOrderByQuery2()
  {
    $stmt = new Sabel_DB_Mysql_Statement();
    $stmt->type(Sabel_DB_Statement::SELECT);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo("student"));
    $stmt->constraints(array("order" => "id DESC, name ASC"));
    $expected = "SELECT `id`, `name` FROM `student` ORDER BY `id` DESC, `name` ASC";
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testEscapeString()
  {
    $stmt = Sabel_DB::createStatement("default");
    $this->assertEquals(array("'a\'b\\\\z'"), $stmt->escape(array("a'b\z")));
  }
  
  public function testClose()
  {
    Sabel_DB_Metadata::clear();
    Sabel_DB_Connection::closeAll();
  }
}
