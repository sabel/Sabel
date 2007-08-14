<?php

class Test_DB_Ibase extends Test_DB_Test
{
  private static $params1 = array("driver"   => "ibase",
                                  "host"     => "localhost",
                                  "user"     => "develop",
                                  "password" => "develop",
                                  "database" => "/home/firebird/sdb_test.fdb");

  private static $params2 = array("driver"   => "ibase",
                                  "host"     => "localhost",
                                  "user"     => "develop",
                                  "password" => "develop",
                                  "database" => "/home/firebird/sdb_test2.fdb");

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
    Sabel_DB_Config::regist("default",  self::$params1);
    //Sabel_DB_Config::regist("default2", self::$params2);

    Test_DB_Test::$db = "IBASE";
  }
}
