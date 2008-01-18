<?php

class Test_DB_PdoPgsql extends Test_DB_Test
{
  private static $params1 = array("package"  => "sabel.db.pdo.pgsql",
                                  "host"     => "localhost",
                                  "user"     => "pgsql",
                                  "password" => "pgsql",
                                  "database" => "sdb_test");

  public static function main()
  {
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("Test_DB_PdoPgsql");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return self::createSuite("Test_DB_PdoPgsql");
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
    Sabel_DB_Config::add("default",  self::$params1);
    Test_DB_Test::$db = "PGSQL";
  }
}
