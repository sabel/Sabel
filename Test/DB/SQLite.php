<?php

class Test_DB_SQLite extends Test_DB_Test
{
  private static $params1 = array("package"  => "sabel.db.pdo.sqlite",
                                  "database" => "/usr/local/lib/php/Sabel/Test/data/sdb_test.sq3");

  public static function main()
  {
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("Test_DB_SQLite");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return self::createSuite("Test_DB_SQLite");
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
    Test_DB_Test::$db = "SQLITE";
  }
}
