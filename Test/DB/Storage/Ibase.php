<?php

/**
 * @category  Storage
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Storage_Ibase extends Test_DB_Storage_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Storage_Ibase");
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.ibase",
                    "host"     => "localhost",
                    "user"     => "develop",
                    "password" => "develop",
                    "database" => "/home/firebird/sdb_test.fdb");
    
    Sabel_DB_Config::add("default", $params);
    
    MODEL("SblStorage")->delete();
  }
}
