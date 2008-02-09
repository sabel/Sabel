<?php

/**
 * testcase for sabel.db.mysql.*
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Mysql extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Mysql");
  }
  
  public function testConnectionRefused()
  {
    $params = array("package"  => "sabel.db.mysql",
                    "host"     => "127.0.0.1",
                    "user"     => "hogehoge",
                    "password" => "fugafuga",
                    "database" => "sdb_test");
    
    Sabel_DB_Config::add("conrefused", $params);
    $driver = new Sabel_DB_Mysql_Driver("conrefused");
    
    try {
      $resource = Sabel_DB_Connection::connect($driver);
    } catch (Sabel_DB_Exception_Connection $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.mysql",
                    "host"     => "127.0.0.1",
                    "user"     => "root",
                    "password" => "",
                    "database" => "sdb_test");
    
    Sabel_DB_Config::add("default", $params);
    Test_DB_Test::$db = "MYSQL";
  }
}
