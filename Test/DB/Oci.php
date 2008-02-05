<?php

/**
 * testcase for sabel.db.oci.*
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Oci extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Oci");
  }
  
  public function testConnectionRefused()
  {
    $params = array("package"  => "sabel.db.oci",
                    "host"     => "127.0.0.1",
                    "user"     => "hogehoge",
                    "password" => "fugafuga",
                    "database" => "xe");
    
    Sabel_DB_Config::add("conrefused", $params);
    $driver = new Sabel_DB_Oci_Driver("conrefused");
    
    try {
      $resource = Sabel_DB_Connection::connect($driver);
    } catch (Sabel_DB_Connection_Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.oci",
                    "host"     => "127.0.0.1",
                    "user"     => "develop",
                    "password" => "develop",
                    "database" => "xe");
    
    Sabel_DB_Config::add("default", $params);
    Test_DB_Test::$db = "ORACLE";
  }
}
