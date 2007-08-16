<?php

class Test_DB_Oci extends Test_DB_Test
{
  private static $params1 = array("driver"   => "oci",
                                  "host"     => "127.0.0.1",
                                  "user"     => "develop",
                                  "password" => "develop",
                                  "database" => "xe");

  public static function main()
  {
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("Test_DB_Oci");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return self::createSuite("Test_DB_Oci");
  }

  public function __construct()
  {
  }

  protected function setUp()
  {
  }

  protected function tearDown()
  {
  }

  public function testInit()
  {
    Sabel_DB_Config::regist("default",  self::$params1);
    //Sabel_DB_Config::regist("default2", self::$params2);

    Test_DB_Test::$db = "ORACLE";
  }
}
