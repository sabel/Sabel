<?php

/**
 * @category  Storage
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Storage_PdoMysql extends Test_DB_Storage_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Storage_PdoMysql");
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.pdo.mysql",
                    "host"     => "127.0.0.1",
                    "user"     => "root",
                    "password" => "",
                    "database" => "sdb_test");
    
    Sabel_DB_Config::add("default", $params);
    
    MODEL("SblStorage")->delete();
  }
}
