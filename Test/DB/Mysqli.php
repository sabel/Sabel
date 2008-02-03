<?php

class Test_DB_Mysqli extends Test_DB_Test
{
  private static $params1 = array("package"  => "sabel.db.mysqli",
                                  "host"     => "127.0.0.1",
                                  "user"     => "root",
                                  "password" => "",
                                  "database" => "sdb_test");

  public static function main()
  {
    require_once "PHPUnit/TextUI/TestRunner.php";
    
    $suite  = new PHPUnit_Framework_TestSuite("Test_DB_Mysqli");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return self::createSuite("Test_DB_Mysqli");
  }
  
  public function testInit()
  {
    Sabel_DB_Config::add("default",  self::$params1);
    Test_DB_Test::$db = "MYSQL";
  }
}
