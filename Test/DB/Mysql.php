<?php

class Test_DB_Mysql extends Test_DB_Test
{
  private static $params1 = array("driver"   => "mysql",
                                  "host"     => "127.0.0.1",
                                  "user"     => "root",
                                  "password" => "",
                                  "database" => "sdb_test");

  private static $params2 = array("driver"   => "pdo-mysql",
                                  "host"     => "127.0.0.1",
                                  "user"     => "root",
                                  "password" => "",
                                  "database" => "sdb_test2");

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

    Test_DB_Test::$db = "MYSQL";

    $tables   = Test_DB_Test::$tables;
    $executer = new Sabel_DB_Model_Executer("Member");

    foreach ($tables as $table) {
      $executer->query("DELETE FROM $table");
    }
  }
}
