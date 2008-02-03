<?php

class Test_DB_Mysql extends Test_DB_Test
{
  private static $params1 = array("package"  => "sabel.db.mysql",
                                  "host"     => "127.0.0.1",
                                  "user"     => "root",
                                  "password" => "",
                                  "database" => "sdb_test");

  public static function main()
  {
    require_once "PHPUnit/TextUI/TestRunner.php";
    
    $suite  = new PHPUnit_Framework_TestSuite("Test_DB_Mysql");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
  }
  
  public static function suite()
  {
    return self::createSuite("Test_DB_Mysql");
  }
  
  public function testInit()
  {
    Sabel_DB_Config::add("default",  self::$params1);
    Test_DB_Test::$db = "MYSQL";
  }
}
