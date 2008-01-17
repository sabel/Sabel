<?php

class Test_DB_Ibase extends Test_DB_Test
{
  private static $params1 = array("package"  => "sabel.db.ibase",
                                  "host"     => "localhost",
                                  "user"     => "develop",
                                  "password" => "develop",
                                  "database" => "/home/firebird/sdb_test.fdb");

  public static function main()
  {
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("Test_DB_Ibase");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return self::createSuite("Test_DB_Ibase");
  }

  public function __construct()
  {
  }

  public function setUp()
  {
  }

  public function tearDown()
  {
  }

  public function testInit()
  {
    Sabel_DB_Config::add("default",  self::$params1);
    Test_DB_Test::$db = "IBASE";
  }
  
  public function testDefinedValue()
  {
    $this->assertEquals(8, IBASE_COMMITTED);
    $this->assertEquals(32, IBASE_REC_NO_VERSION);
    $this->assertEquals(40, IBASE_COMMITTED|IBASE_REC_NO_VERSION);
  }
}
