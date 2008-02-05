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
    $params = array("package"  => "sabel.db.pgsql",
                    "host"     => "localhost",
                    "user"     => "hogehoge",
                    "password" => "fugafuga",
                    "database" => "sdb_test");
    
    Sabel_DB_Config::add("conrefused", $params);
    $driver = new Sabel_DB_Pgsql_Driver("conrefused");
    
    try {
      $resource = Sabel_DB_Connection::connect($driver);
    } catch (Sabel_DB_Connection_Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.pgsql",
                    "host"     => "localhost",
                    "user"     => "pgsql",
                    "password" => "pgsql",
                    "database" => "sdb_test");
    
    Sabel_DB_Config::add("default", $params);
    Test_DB_Test::$db = "PGSQL";
  }
}
