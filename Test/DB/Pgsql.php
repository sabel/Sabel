<?php

/**
 * testcase for sabel.db.pgsql.*
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Pgsql extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Pgsql");
  }
  
  public function testConnectionRefused()
  {
    return;
    $params = array("package"  => "sabel.db.pgsql",
                    "host"     => "localhost",
                    "user"     => "hogehoge",
                    "password" => "fugafuga",
                    "database" => "sdb_test");
    
    Sabel_DB_Config::add("conrefused", $params);
    $driver = new Sabel_DB_Pgsql_Driver("conrefused");
    
    try {
      $c = error_reporting(0);
      $resource = Sabel_DB_Connection::connect($driver);
      error_reporting($c);
    } catch (Sabel_DB_Exception_Connection $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testInit()
  {
    Sabel_DB_Config::add("default", Test_DB_TestConfig::getPgsqlConfig());
    Test_DB_Test::$db = "PGSQL";
  }
}
