<?php

/**
 * @category  Storage
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Storage_Pgsql extends Test_DB_Storage_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Storage_Pgsql");
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.pgsql",
                    "host"     => "localhost",
                    "user"     => "pgsql",
                    "password" => "pgsql",
                    "database" => "sdb_test");
    
    Sabel_DB_Config::add("default", $params);
    
    MODEL("SblStorage")->delete();
  }
}
